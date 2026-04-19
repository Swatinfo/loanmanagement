# Loan Lifecycle — Roles × Actions

Per-stage reference of who does what, what form fields are captured, and what transitions happen. Use this alongside `workflow-developer.md` (dev internals) and `workflow-guide.md` (user narrative).

## Quick role legend

| Role | Slug | Typical responsibility |
|---|---|---|
| Loan Advisor | `loan_advisor` | Own the loan; collect docs; shepherd through stages |
| Branch Manager | `branch_manager` | Branch oversight; approve at sanction decision |
| BDH | `bdh` | Business Development Head; escalation target above BM |
| Bank Employee | `bank_employee` | Bank-side work: app number, BSM/OSV, rate/PF review, sanction generation, KFS, e-sign |
| Office Employee | `office_employee` | Office ops: technical valuation, docket review, OTC clearance, post-sanction paperwork |
| Task Owner | `task_owner` | Virtual role — resolves to loan's `assigned_advisor` or `created_by` |
| Super Admin | `super_admin` | System-wide bypass |
| Admin | `admin` | System admin |

Multi-phase stages often ping-pong between two or three roles.

## Stage 1 — Inquiry

- **Sequence**: 1
- **Default role**: `loan_advisor`
- **Actions**: marked complete on loan creation (for quotation-converted loans, auto-completed together with Document Selection)
- **Data captured**: none beyond the base `loan_details` (customer, amount, bank)
- **Transition out**: completes immediately (auto) → moves to Document Selection

## Stage 2 — Document Selection

- **Sequence**: 2
- **Default role**: `loan_advisor`
- **Actions**: auto-completed for quotation-converted loans; for direct-created loans the advisor confirms the template from config defaults
- **Data captured**: creates `loan_documents` rows from the quotation's document list OR from `config('app-defaults.documents_{en|gu}.{customer_type}')`
- **Transition out**: completes → moves to Document Collection

## Stage 3 — Document Collection

- **Sequence**: 3
- **Default role**: `loan_advisor`
- **Actions per document**:
  - Mark **received** (sets `received_date`, `received_by`)
  - Mark **rejected** (captures `rejected_reason`)
  - Mark **waived** (for non-required docs)
  - Upload file (stores under `storage/app/loan-documents/{loanId}/{docId}_{ts}.{ext}`; if pending, auto-marks received)
  - Delete file (keeps record)
  - Add custom document (`POST /loans/{loan}/documents`)
  - Remove custom document
- **Completion**: auto-completes when `LoanDocumentService::allRequiredResolved()` returns true (all required docs received or waived)
- **Soft revert**: editing a doc back to pending/rejected soft-reverts the stage to in_progress and cascades to pending on later stages
- **Transition out**: completes → opens Parallel Processing (4)

## Stage 4 — Parallel Processing (parent)

- **Sequence**: 4
- **`is_parallel`**: true (this is the parent container)
- **Direct actions**: none on the parent itself — it just represents the group
- **Children** (stages 4a–4e below) run in two waves:
  - **Wave A (sequential)**: `app_number` → `bsm_osv`
  - **Wave B (parallel)**: `legal_verification`, `technical_valuation`, `sanction_decision` (all start when `bsm_osv` completes)
- **Completion**: when all 5 sub-stages are resolved (completed/skipped/rejected), the parent auto-completes. Flag-off: flow advances to Rate & PF (requires `is_sanctioned = true`). Flag-on (`OPEN_RATE_PF_PARALLEL=1`): Rate & PF has been in progress since BSM/OSV; Sanction Letter advances when both Rate & PF and parallel_processing are complete.

### Stage 4a — Application Number

- **Sequence inside parallel**: first
- **Default role**: `bank_employee`
- **Form fields** (stored in `StageAssignment.notes`):
  - `application_number` (required, max 50) — also mirrored to `loan_details.application_number`
  - `docket_days_offset` (required int ≥0)
  - `custom_docket_date` (required_if `docket_days_offset=0`)
- **Logic**: when Sanction completes, `handleStageCompletion` reads these fields to compute `loan_details.expected_docket_date`
- **Actions**: Save Details (auto-completes on required fields); Raise Query; Transfer
- **Transition out**: completes → triggers `bsm_osv` start (only this sub-stage)

### Stage 4b — BSM / OSV

- **Sequence inside parallel**: second (waits for 4a)
- **Default role**: `bank_employee`
- **Form fields**: free-form notes (no strict required fields; may be bank-specific)
- **Actions**: Complete; Raise Query; Transfer
- **Transition out**: completes → unlocks remaining parallel subs (4c, 4d, 4e) simultaneously

### Stage 4c — Legal Verification (3-phase)

- **Default phase roles** (from `Stage.sub_actions`):
  - Phase 1: `loan_advisor` (or task_owner)
  - Phase 2: `bank_employee`
  - Phase 3: `loan_advisor` (back to owner for review)
- **Action endpoint**: `POST /loans/{loan}/stages/legal_verification/action`
- **Phase transitions**:
  - Phase 1 → **send_to_bank** action — advisor sends, stage transfers to bank phase role (records `suggested_legal_advisor` if provided)
  - Phase 2 → **initiate_legal** action — bank confirms legal advisor (`confirmed_legal_advisor`), transfers back to phase 3 (owner)
  - Phase 3 → completion via Save Details or explicit Complete
- **Notes keys written**: `legal_phase`, `legal_original_assignee`, `suggested_legal_advisor`, `confirmed_legal_advisor`

### Stage 4d — Technical Valuation (2-phase)

- **Default phase roles**:
  - Phase 1: `loan_advisor`
  - Phase 2: `office_employee`
- **Action endpoint**: `POST /loans/{loan}/stages/technical_valuation/action`
- **Phase transitions**:
  - Phase 1 → **send_to_office** action — transfers to office
  - Phase 2 → completion triggered automatically when the valuation form is saved at `POST /loans/{loan}/valuation` (creates/updates a `valuation_details` row)
- **Cross-reference**: see `LoanValuationController` and `loans.md`

### Stage 4e — Sanction Decision (decision gate)

- **`stage_type`**: `decision`
- **Default role**: `branch_manager` (escalates to `bdh` per the controller logic)
- **Action endpoint**: `POST /loans/{loan}/stages/sanction_decision/action`
- **Actions**:
  - **approve** — sets `loan_details.is_sanctioned = true`, marks this stage completed. When all parallel subs are done, the parent completes. Flag-off: flow moves to Rate & PF. Flag-on: Rate & PF is already running since BSM/OSV and Sanction Letter opens once both are complete.
  - **escalate_to_bm** — requires `decision_remarks` (min 1 char). Appends to `escalation_history` JSON with timestamp; transfers to a BM.
  - **escalate_to_bdh** — same, escalates to BDH.
  - **reject** — requires `rejection_reason` (min 10 chars). Restricted to `super_admin` / `admin` / `branch_manager` / `bdh`. Rejects ALL pending/in_progress stages on the loan in one shot.

## Stage 5 — Rate & PF (3-phase)

- Sequence: 5. Flag-off: after Parallel Processing with `is_sanctioned=true`. Flag-on (`OPEN_RATE_PF_PARALLEL=1`): opens in parallel with legal/technical/sanction_decision after BSM/OSV completes; no `is_sanctioned` gate.
- **sub_actions entries**: 3 (one per runtime phase — normalized 2026-04-18).
- **Default phase roles** (via `sub_actions[i].role` — zero-indexed):
  - Phase 1 / index 0: `task_owner` (owner fills form)
  - Phase 2 / index 1: `bank_employee` (reviews rates)
  - Phase 3 / index 2: `task_owner` (finalizes PF + charges)
- **Action endpoint**: `POST /loans/{loan}/stages/rate_pf/action`
- **Required form fields** (checked before send_to_bank):
  - `interest_rate` (decimal)
  - `repo_rate` (decimal)
  - `bank_rate` (decimal)
  - `rate_offered_date` (date)
  - `rate_valid_until` (date)
  - `processing_fee_type` (flat / percent)
  - `processing_fee` (number)
  - `gst_percent` (number, typically 18)
  - `admin_charges` (number)
  - `admin_charges_gst_percent` (number)
- **Actions**:
  - **send_to_bank** (Phase 1 → 2)
  - **return_to_owner** (Phase 2 → 3) — used when bank requests changes
  - **complete** (Phase 3 → done)

## Stage 6 — Sanction Letter (3-phase)

- **Sequence**: 6
- **Default phase roles**:
  - Phase 1: `loan_advisor`
  - Phase 2: `bank_employee` (generates sanction letter)
  - Phase 3: `loan_advisor` (captures details back)
- **Action endpoint**: `POST /loans/{loan}/stages/sanction/action`
- **Actions**:
  - **send_for_sanction** (Phase 1 → 2)
  - **sanction_generated** (Phase 2 → 3)
  - Phase 3 completion via Save Details with required fields
- **Required fields at Phase 3** (via `saveNotes`):
  - `sanction_date` (date)
  - `sanctioned_amount` (int)
  - `tenure_months` (int)
  - `emi_amount` (int)
  - Sanity check: `emi_amount ≤ sanctioned_amount`
- **Side effect on completion**: reads Application Number stage for `docket_days_offset` / `custom_docket_date` and updates `loan_details.expected_docket_date`
- **Notes keys**: `sanction_phase`, `sanction_original_assignee`

## Stage 7 — Docket Login (3-phase)

- **Sequence**: 7
- **Default phase roles**:
  - Phase 1: `loan_advisor`
  - Phase 2: `bank_employee`
  - Phase 3: `office_employee` (review and prep KFS)
- **Action endpoint**: `POST /loans/{loan}/stages/docket/action`
- **Actions**: **send_to_office** (Phase 1 → 2, etc.)
- **Required fields at Phase 2**: `login_date`

## Stage 8 — KFS

- **Sequence**: 8
- **Default role**: `bank_employee`
- **Stage type**: single-phase
- **Actions**: Complete; Raise Query; Transfer
- **Form**: bank-provided KFS details (free-form notes)

## Stage 9 — E-Sign & eNACH (4-phase)

- **Sequence**: 9
- **Default phase roles**:
  - Phase 1: `loan_advisor`
  - Phase 2: `bank_employee`
  - Phase 3: `office_employee` (customer-facing signing coordination)
  - Phase 4: `loan_advisor` (confirm complete)
- **Action endpoint**: `POST /loans/{loan}/stages/esign/action`
- **Actions**:
  - **send_for_esign** (Phase 1 → 2)
  - **esign_generated** (Phase 2 → 3)
  - **esign_customer_done** (Phase 3 → 4)
  - **esign_complete** (Phase 4 → done, calls `updateStageStatus(..., 'completed')`)

## Stage 10 — Disbursement

- **Sequence**: 10
- **Default role**: `loan_advisor` (task_owner)
- **Form**: `LoanDisbursementController` at `GET/POST /loans/{loan}/disbursement`
- **Fields**:
  - `disbursement_type` — `fund_transfer` or `cheque`
  - `disbursement_date` (d/m/Y → Y-m-d)
  - `amount_disbursed` (int, min 1)
  - `bank_account_number` (max 50)
  - If cheque: `cheques[]` array of `{cheque_name, cheque_number, cheque_date, cheque_amount}`; sum ≤ `amount_disbursed`
- **Stage completion** handled by `DisbursementService::processDisbursement`:
  - **fund_transfer** → skips OTC (marks OTC `skipped`), marks loan `completed`, notifies creator + advisor
  - **cheque** → opens OTC Clearance stage
- **Locked** if `loan.status` ≠ active / on_hold

## Stage 11 — OTC Clearance (cheque only)

- **Sequence**: 11
- **Default role**: `office_employee`
- **Skipped entirely** when disbursement was fund_transfer
- **Required fields**: `handover_date`
- **Actions**: Save Details (auto-completes on `handover_date` present); Transfer
- **On complete**: marks loan `completed`, notifies creator + advisor

## Cross-cutting actions (available on most in-progress stages)

| Action | Route / endpoint | Permissions | Notes |
|---|---|---|---|
| **Update status** | `POST /loans/{loan}/stages/{stageKey}/status` | `manage_loan_stages` (+ `skip_loan_stages` for skipped) | State machine — see `StageAssignment::canTransitionTo` |
| **Assign** | `POST /loans/{loan}/stages/{stageKey}/assign` | `manage_loan_stages` | Sets `assigned_to` for a specific user |
| **Transfer** | `POST /loans/{loan}/stages/{stageKey}/transfer` | `manage_loan_stages` + `transfer_loan_stages` | Reassigns + writes StageTransfer ledger; reassigns active queries |
| **Skip** | `POST /loans/{loan}/stages/{stageKey}/skip` | `skip_loan_stages` | Disabled by default (migration 2026_04_13_124552) |
| **Reject** | `POST /loans/{loan}/stages/{stageKey}/reject` | `manage_loan_stages` (+ super_admin/admin/branch_manager/bdh only) | Marks loan rejected, sets rejected_* fields |
| **Raise query** | `POST /loans/{loan}/stages/{stageKey}/query` | `manage_loan_stages` | Creates `StageQuery` with status=pending. **Blocks stage completion.** |
| **Respond to query** | `POST /loans/queries/{query}/respond` | `manage_loan_stages` | Creates `QueryResponse`; sets query status=responded; notifies raiser |
| **Resolve query** | `POST /loans/queries/{query}/resolve` | `manage_loan_stages` | Only the raiser; status=resolved |
| **Save notes** | `POST /loans/{loan}/stages/{stageKey}/notes` | `manage_loan_stages` | Per-stage form data; may auto-advance or soft-revert |
| **Eligible users picker** | `GET /loans/{loan}/stages/{stageKey}/eligible-users?role=` | `manage_loan_stages` | JSON for transfer dropdown |
| **Add remark** | `POST /loans/{loan}/remarks` | `add_remarks` | Optional `stage_key`; nullable = general remark |

## Loan-level status actions (outside the stage engine)

Gated by `edit_loan` + role constraints.

| Transition | Elevated permissions required? |
|---|---|
| active → on_hold | No |
| active → cancelled | super_admin / admin / branch_manager / bdh |
| on_hold → active | No |
| cancelled → active | super_admin / admin / branch_manager / bdh |
| rejected → active | super_admin / admin / branch_manager / bdh (restores rejected stages to in_progress) |

All transitions require a reason (except reactivation). Changes logged to `activity_logs`.

## Who-notifies-whom summary

| Event | Recipients |
|---|---|
| Stage assigned | the new assignee |
| Stage completed | loan creator + advisor (minus the current user) |
| Query raised | stage assignee |
| Query responded | query raiser |
| Query resolved | (no notification) |
| Loan completed | loan creator + advisor (minus the current user) |

## See also

- `.docs/workflow-developer.md` — engine internals (snapshot, state machine, orchestration)
- `.docs/workflow-guide.md` — end-user narrative
- `.docs/user-assignment.md` — how a role resolves to a specific user
- `.claude/services-reference.md` — `LoanStageService`, `StageQueryService`, `DisbursementService`
- `.claude/routes-reference.md` — full route table for stage endpoints
