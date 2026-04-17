# Workflow — Developer Guide

The loan workflow engine: stages, phases, transfers, queries, auto-assignment, and parallel orchestration. Core code is in `app/Services/LoanStageService.php` + `app/Http/Controllers/LoanStageController.php`.

## Model primitives

| Model | Role |
|---|---|
| `Stage` | master stage definition (name, sequence, parent, sub_actions, default_role) |
| `BankStageConfig` | per-bank override of `assigned_role` / `phase_roles` |
| `ProductStage` | per-product config (enabled, default user, per-phase overrides) |
| `ProductStageUser` | per-product per-branch/location/phase user assignment |
| `StageAssignment` | **instance** — one per (loan, stage_key) |
| `StageTransfer` | ledger row — every (re)assignment of an instance |
| `StageQuery`, `QueryResponse` | two-way blocking queries on a stage |
| `LoanProgress` | 1:1 with loan — total/completed counts, snapshot |

## Stage layout

```
1. inquiry
2. document_selection
3. document_collection
4. parallel_processing  (parent; is_parallel = true)
   ├─ 4a. app_number       (sequential start-gate for 4b)
   ├─ 4b. bsm_osv          (sequential start-gate for 4c/4d/4e)
   ├─ 4c. legal_verification    (3-phase)
   ├─ 4d. technical_valuation   (2-phase)
   └─ 4e. sanction_decision     (decision: approve / escalate / reject)
5. rate_pf                 (3-phase) — unlocked after parallel_processing completes AND is_sanctioned = true
6. sanction                (3-phase)
7. docket                  (3-phase)
8. kfs
9. esign                   (4-phase)
10. disbursement
11. otc_clearance          (cheque only — skipped for fund_transfer)
```

Sub-stages of `parallel_processing` set `parent_stage_key = parallel_processing` and `is_parallel_stage = true` on their `StageAssignment`.

## Multi-phase stages

A stage with multiple phases uses `Stage.sub_actions` (JSON array) — each entry defines a `name` and `role`:

```json
[
  { "name": "send_for_sanction",   "role": "loan_advisor" },
  { "name": "sanction_generated",  "role": "bank_employee" },
  { "name": "sanction_details",    "role": "loan_advisor" }
]
```

Per-bank override: `BankStageConfig.phase_roles = {"0": "office_employee", "1": ..., "2": ...}`.

Per-product override: `ProductStage.sub_actions_override = [{...}]`.

Phase state machine lives inside `StageAssignment.notes` — a JSON blob with keys like `{stage}_phase: 2`, `{stage}_original_assignee: {userId}`, plus form data.

Phase transitions are performed by dedicated action endpoints (e.g., `POST /loans/{loan}/stages/sanction/action`) implemented in `LoanStageController`. Each action:

1. Reads current phase from notes
2. Validates the requested action against the phase
3. Transfers the stage to the next phase's role (via `LoanStageService::transferStage`)
4. Updates notes (`sanction_phase`, `sanction_original_assignee`, etc.)
5. On final-phase completion, calls `updateStageStatus(..., 'completed')`

## Role resolution (summary)

Full detail in `user-assignment.md` and `.claude/services-reference.md`. Recap:

- `resolveStageRole(stageKey, bankId)`: bank override → Stage.assigned_role → `task_owner`
- `resolvePhaseRole(stageKey, phaseIndex, bankId)`: bank override → Stage.sub_actions[i].role → `task_owner`
- `findUserForRole(role, loan, stageKey, ?phaseIndex)`: snapshot default → role resolver (bank_employee / office_employee / task_owner)

The **workflow snapshot** is built at loan creation via `buildWorkflowSnapshot()` and stored on `loan_details.workflow_config`. It freezes `role` + `default_user_id` per stage/phase so admin reconfig doesn't disrupt in-flight loans.

## Initialization

`LoanStageService::initializeStages($loan)`:

1. Define all base stage keys (14 total incl. sub-stages)
2. Fetch enabled `Stage` rows matching keys
3. Bulk-insert `StageAssignment` for each (status=pending, priority=normal)
4. Count main (non-parallel) stages, create `LoanProgress` (total_stages, completed_stages=0)

`autoCompleteStages($loan, array $keys)` — for quotation conversion, marks `inquiry` + `document_selection` completed immediately.

## State machine — `StageAssignment::canTransitionTo`

Valid transitions:

| From | To |
|---|---|
| pending | in_progress, skipped |
| in_progress | completed, rejected |
| rejected | in_progress (reactivate flow) |
| completed | in_progress (soft-revert) |
| skipped | (no transitions) |

Enforced inside `LoanStageService::updateStageStatus()`. Additionally:

- Block completion if stage has active queries (`hasPendingQueries()`)
- On completion: run `handleStageCompletion()` for post-completion orchestration

## Completion orchestration — `handleStageCompletion`

This is the heart of the engine. Behaviour per completed stage:

### `app_number`

→ `startSingleParallelSubStage('bsm_osv')` — starts only bsm_osv; other parallel subs wait.

### `bsm_osv`

→ `startRemainingParallelSubStages()` — marks `legal_verification`, `technical_valuation`, `sanction_decision` as in_progress and auto-assigns each.

### Any parallel sub-stage

→ `checkParallelCompletion()` — if all sub-stages of `parallel_processing` are done (completed/skipped/rejected), mark parent `parallel_processing` completed, then advance to next main stage.

### `sanction`

Read `app_number` stage notes for:
- `custom_docket_date` — explicit override
- `docket_days_offset` — days from sanction date

Write `loan.expected_docket_date` accordingly.

### `disbursement`

- If `disbursement_details.disbursement_type === 'fund_transfer'`:
  - Mark `otc_clearance` as skipped
  - Mark loan `status = completed`
  - Notify creator + advisor
  - **Return early** (skip sequential advance)
- Else (cheque): advance to `otc_clearance` normally.

### `otc_clearance`

→ Mark loan `status = completed`, notify users.

### Default path

Sequential advance: find next main stage, call `assignNextStage()` which auto-assigns using the snapshot.

## Soft revert

`revertStageIfIncomplete($loan, $stageKey, $isStillComplete)` — e.g., when documents are marked received then unmarked:

1. If `isStillComplete` — no-op
2. Revert current stage from completed → in_progress
3. Revert all subsequent pending stages back to pending (they shouldn't have progressed without the prior being complete)
4. For `parallel_processing` parent: also revert each sub-stage to pending
5. Update `loan.current_stage`
6. Recalculate progress

Called by `saveNotes` in `LoanStageController` and by `LoanDocumentController@updateStatus`.

## Transfer

`transferStage($loan, $stageKey, int $toUserId, ?string $reason)`:

1. Update `stage_assignments.assigned_to`
2. Insert `stage_transfers` (manual type)
3. Reassign all active queries (pending/responded) to the new stage_assignment context
4. Log activity
5. Touch `loan.updated_at`

Requires both `manage_loan_stages` + `transfer_loan_stages`.

## Rejection

`rejectLoan($loan, $stageKey, $reason, ?int $userId)`:

1. Set `loan.status = rejected`, `rejected_at`, `rejected_by`, `rejected_stage`, `rejection_reason`
2. Mark current stage assignment `status = rejected`
3. Log activity

Restricted to super_admin / admin / branch_manager / bdh in the controller. Elsewhere, the `sanction_decision` stage has its own rejection path (`sanctionDecisionAction` action=`reject`), which also rejects **all pending/in_progress stages** at once.

## Queries (blocking)

From the stage context, any assigned user can raise a query (usually back to the advisor). The query:

- Creates `stage_queries` row (status=pending)
- Notifies the stage assignee
- **Blocks stage completion** — `updateStageStatus()` checks `hasPendingQueries()` before allowing completion

Flow:
- `POST /loans/{loan}/stages/{stageKey}/query` — raise
- `POST /loans/queries/{query}/respond` — response (status → responded; notifies raiser)
- `POST /loans/queries/{query}/resolve` — resolver is the raiser (status → resolved)

Only `resolved` queries stop blocking. UI shows active queries count on the stage card.

## Controller action endpoints (multi-phase)

Defined in `LoanStageController`. Each is an idempotent HTTP POST that advances phase state:

| Endpoint | Actions | Phases |
|---|---|---|
| `sanction-action` | send_for_sanction, sanction_generated | 3 phases, plus `saveNotes` handles Phase-3 form completion |
| `legal-action` | send_to_bank, initiate_legal | 3 phases (optional advisor handoff) |
| `technical-valuation-action` | send_to_office | 2 phases |
| `docket-action` | send_to_office | 3 phases |
| `rate-pf-action` | send_to_bank, return_to_owner, complete | 3 phases |
| `esign-action` | send_for_esign, esign_generated, esign_customer_done, esign_complete | 4 phases (Phase 4 = completion) |
| `sanction-decision-action` | approve, escalate_to_bm, escalate_to_bdh, reject | decision gate |

Validation, notes updates, and transfer target calculations are specific to each. Read the controller for specifics; the pattern is the same throughout.

## Save notes

`POST /loans/{loan}/stages/{stageKey}/notes` (`saveNotes`) persists per-stage form data into `StageAssignment.notes` JSON. Logic:

1. Validate `notes_data` array present
2. Per-stage validation of required fields via `getFieldErrors($stageKey, $data)`. Examples:
   - `app_number`: requires `application_number`, `docket_days_offset` (with optional `custom_docket_date` if offset = 0)
   - `sanction`: requires `sanction_date`, `sanctioned_amount`, `tenure_months`, `emi_amount` (Phase 3 only)
   - `docket`: `login_date` (Phase 2 only)
   - `otc_clearance`: `handover_date`
3. Domain-specific checks:
   - Sanction: EMI ≤ sanctioned_amount sanity
   - Application number: also write to `loan_details.application_number`
   - Sanction date with offset: recompute `expected_docket_date`
4. If stage is pending/in_progress AND data now complete (`isStageDataComplete`), auto-advance to in_progress then completed
5. If stage is completed AND data no longer complete, soft-revert

## Eligible users picker

`GET /loans/{loan}/stages/{stageKey}/eligible-users?role=`:

Returns a JSON list of users who could be assigned to the stage, filtered by:

- `bank_employee`: by loan's `bank_id`
- `office_employee`: by loan's `bank_id` (via bank_employees pivot) and branch
- Branch-based roles: by `loan.branch_id`

Response: `{ users: [{id, name, badge}], default_user_id }` — UI uses this for the transfer dropdown.

## Progress recalculation

`recalculateProgress($loan)`:

- Count completed main stages
- Overall percentage = completed / total × 100
- `workflow_snapshot` = current `{stage_key: {status, assigned_to}}` map for all stages

Called after every completion / revert / status change.

## Notifications

Integrated via `NotificationService`:

- On stage assignment → `notifyStageAssignment()`
- On stage completion → `notifyStageCompleted()` (fanned out to creator + advisor, excluding current user)
- On loan completion → `notifyLoanCompleted()`

Queries add separate notifications on raise + respond.

## Gotchas

- Always assign stages via `assignStage()` or `transferStage()`, not direct updates — they write the `stage_transfers` ledger and reassign queries.
- `initializeStages` seeds `priority = 'normal'` for all. There's no UI to bump priority; add one if that matters.
- `workflow_config` snapshot is frozen. If business rules change role assignments mid-flight, you'd need to rebuild the snapshot (not implemented yet).
- Skip is permission-gated (`skip_loan_stages`); the default assignment is **disabled for all roles** (migration `2026_04_13_124552`). Re-enable deliberately.
- `EnsureUserIsActive` middleware is global — deactivated assignees lose access on next request but their assignments don't move. You must transfer manually.

## See also

- `.claude/services-reference.md` — full `LoanStageService` method list
- `user-assignment.md` — role → user resolution details
- `loans.md` — CRUD, docs, valuation, disbursement
- `workflow-guide.md` — user-facing narrative
