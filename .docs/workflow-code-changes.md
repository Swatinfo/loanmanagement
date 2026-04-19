# Workflow Code Changes — Step-by-Step Guide

How to modify stage behavior: opening stages, parallel sub-stages, completion conditions, phase flow, and side-effects. Pair this with `.docs/workflow-developer.md` (architecture reference).

**Worked example in the wild:** the `open_rate_pf_parallel` feature flag (see `.docs/workflow-developer.md` → "Feature flag: `app.open_rate_pf_parallel`") implements Recipes 3 + 6 together — gating `rate_pf` on an earlier predecessor and gating `sanction` on multiple completions. Read that section alongside these recipes for a concrete reference.

---

## Pre-flight — Always Do First

1. Read `.docs/workflow-developer.md` (engine architecture)
2. Read `.claude/services-reference.md` (service methods)
3. Read `.claude/database-schema.md` (stages, stage_assignments, stage_transfers tables)
4. Read `tasks/lessons.md` (past corrections)
5. Write plan in `tasks/todo.md` before editing
6. Identify scope: is it master config (new stage?), runtime orchestration (when opens?), or completion rule (what closes it?)

---

## Recipe 1 — Add a New Stage (Sequential)

**Use case:** Insert a new main stage between two existing ones, e.g., "Compliance Check" between `kfs` and `esign`.

### Steps

1. **Create migration** — `php artisan make:migration add_compliance_check_stage --no-interaction`
   - Insert `Stage` row: `stage_key`, `stage_name_en`, `stage_name_gu`, `sequence_order` (between adjacent stages — reorder others if needed), `default_role` (JSON array), `stage_type = 'standard'`, `enabled = true`
   - If multi-phase: set `sub_actions` JSON
2. **Register in initializer** — `app/Services/LoanStageService.php` → `initializeStages()`
   - Add `'compliance_check'` to `$stageKeys` array
3. **Update progress counts** — increment `total_stages` logic in `LoanProgress` creation (it's based on counted main stages; nothing to hard-code if you don't skip the count)
4. **Gate prerequisites** — `LoanStageService::canStartStage()`
   - Add a `case 'compliance_check':` returning `true` only if prior stage completed
5. **Form view** — create `resources/views/loans/stages/_compliance_check.blade.php` (copy a sibling stage, adjust fields)
6. **Wire view** into `resources/views/loans/show.blade.php` or the stage partial dispatcher
7. **Completion condition** — `LoanStageController::getFieldErrors()` + `isStageDataComplete()` — add `case 'compliance_check'` with required fields
8. **Activity + notification** — auto-handled by `handleStageCompletion` default path (sequential advance + `notifyStageAssignment`)
9. **Seed existing loans** (optional) — tinker: `LoanDetail::active()->get()->each(fn($l) => app(LoanStageService::class)->initializeMissingStage($l, 'compliance_check'))` (add helper if needed)
10. **Update docs** — `.docs/workflow-developer.md` stage layout + `.claude/database-schema.md` + `.docs/workflow-guide.md`
11. **Test** — `php artisan make:test --phpunit ComplianceCheckStageTest`; assert initialization, transition gate, auto-advance
12. **Run tests + Pint** — `php artisan test --compact --filter=ComplianceCheck && vendor/bin/pint --dirty --format agent`

---

## Recipe 2 — Add a Parallel Sub-Stage Under `parallel_processing`

**Use case:** Add a new parallel child, e.g., `credit_bureau_check`, running alongside legal/technical/sanction_decision.

### Steps

1. **Create migration** — `Stage` row with:
   - `parent_stage_key = 'parallel_processing'`
   - `is_parallel = true`
   - `sequence_order` (relative to siblings)
   - `default_role`, `sub_actions` (if multi-phase)
2. **Register stage key** — `LoanStageService::initializeStages()` add to `$stageKeys`
3. **Open the sub-stage** — `LoanStageService::handleStageCompletion()`
   - Add `credit_bureau_check` to the list in `startRemainingParallelSubStages()` so it opens after `bsm_osv` completes, OR
   - Gate it behind a different predecessor by adding a custom branch (e.g., after `app_number` by calling `$this->autoAssignStage($loan, 'credit_bureau_check')` in the `case 'app_number'` block)
4. **Parent completion** — `checkParallelCompletion()` already queries by `parent_stage_key` — new child is auto-included
5. **Prerequisite** — `canStartStage()` add `case 'credit_bureau_check'` requiring `bsm_osv` completed
6. **Revert handling** — `revertStageIfIncomplete()` handles parallel children via parent reset; confirm no extra code needed
7. **Form view, controller dispatch, activity log, docs, tests** — same as Recipe 1

---

## Recipe 3 — Change When a Stage Opens

**Use case:** e.g., open `rate_pf` only after sanction_decision = `approved` (the flag-off default; flag-on opens rate_pf earlier, see `.docs/workflow-developer.md` feature-flag section) — or loosen/tighten the condition.

### Two places to edit:

1. **Trigger point** — `LoanStageService::handleStageCompletion()`
   - Find the branch for the stage whose completion should open your target (e.g., `case 'parallel_processing':` opens `rate_pf`)
   - Add/remove calls to `autoAssignStage($loan, 'target_key')` or `assignNextStage()`
2. **Prerequisite guard** — `LoanStageService::canStartStage()`
   - Add/modify the `case 'target_key':` branch — e.g., require `$loan->is_sanctioned && $loan->stageAssignment('some_other')?->status === 'completed'`

Run `php artisan test --compact --filter=StartGate` after.

---

## Recipe 4 — Change Completion Conditions (Required Fields)

**Use case:** `sanction` stage now also requires `bank_reference_number`.

### Steps

1. **Validation** — `LoanStageController::getFieldErrors($stageKey, $data)`
   - Find `case 'sanction':` — add rule for `bank_reference_number` (respect phase conditional if applicable)
2. **Auto-advance check** — `LoanStageController::isStageDataComplete($stageKey, $data)`
   - Mirror the presence check so auto-complete only triggers when the new field is filled
3. **Form view** — `resources/views/loans/stages/_sanction.blade.php` — add the input
4. **Activity properties** — nothing to change; `saveNotes` stores full `notes_data`
5. **Soft revert** — handled automatically: if field is later cleared, `saveNotes` triggers `revertStageIfIncomplete`
6. **Backfill** (if you want existing in-progress loans unblocked) — tinker to prefill notes, or leave users to fill
7. **Test** — new field missing → validation error; present → auto-advance to completed
8. **Update docs** — `.docs/workflow-developer.md` (saveNotes section) + `.docs/workflow-guide.md`

---

## Recipe 5 — Add / Modify a Phase in a Multi-Phase Stage

**Use case:** `esign` needs a 5th phase "legal_review".

### Steps

1. **Update Stage.sub_actions** — migration to append new phase entry to existing `sub_actions` JSON:
   ```php
   DB::table('stages')->where('stage_key', 'esign')->update([
       'sub_actions' => json_encode([
           ['name' => 'send_for_esign',       'role' => 'loan_advisor'],
           ['name' => 'esign_generated',      'role' => 'bank_employee'],
           ['name' => 'esign_customer_done',  'role' => 'loan_advisor'],
           ['name' => 'legal_review',         'role' => 'office_employee'],
           ['name' => 'esign_complete',       'role' => 'loan_advisor'],
       ]),
   ]);
   ```
2. **Controller action** — `LoanStageController::esignAction()`
   - Add a new action branch for `legal_review` — read current phase from notes, transfer to next role via `LoanStageService::transferStage()`, update `esign_phase` in notes
   - Adjust the final-phase completion check (now phase 5 not 4)
3. **Phase-role resolver** — already reads from `Stage.sub_actions[i].role` via `resolvePhaseRole()` — new phase picked up automatically
4. **Bank overrides** — if `BankStageConfig.phase_roles` exists for esign, update those rows to include index 4 role
5. **Per-product phase user** — check `product_stage_users.phase_index` rows; add phase 4 assignments where needed
6. **Workflow snapshot** — existing loans use their frozen `workflow_config`; only new loans get the 5-phase flow. If backfill needed: rebuild snapshots via tinker
7. **View** — update esign form partial to conditionally render legal_review phase UI based on `esign_phase`
8. **Tests** — cover the full 5-phase chain
9. **Docs** — `.docs/workflow-developer.md` multi-phase table, `.docs/workflow-guide.md`

---

## Recipe 6 — Change Post-Completion Side-Effects

**Use case:** When `disbursement` completes with type `fund_transfer`, also auto-generate a loan-closure letter.

### Steps

1. **Edit** `LoanStageService::handleStageCompletion()` → `case 'disbursement':` branch
   - Inside the `fund_transfer` conditional, add the side-effect call (e.g., `app(LoanClosureService::class)->generate($loan)`)
2. **Keep ordering correct** — side-effects before the `return` that skips OTC, so they run for both paths as intended
3. **Activity log + notification** — add explicit `ActivityLog::log()` and `NotificationService` call if the side-effect should be visible in timeline
4. **Tests** — assert letter generation on fund_transfer completion; assert not triggered on cheque path
5. **Update docs** — `.docs/workflow-developer.md` disbursement section

---

## Recipe 7 — Change Role / Assignee Logic

**Use case:** `technical_valuation` should now default to `office_employee` at branch level (was loan_advisor).

### Steps

1. **Master default** — update `Stage.default_role` for `technical_valuation` via migration (array form: `["office_employee"]`)
2. **Bank override** (if any bank keeps old default) — insert/update `bank_stage_configs` row with `assigned_role = 'loan_advisor'` for that bank
3. **Per-product override** — `product_stages.assigned_role` if product-specific
4. **Per-user default** — `product_stage_users` rows (branch/location-scoped)
5. **New-loan snapshot** — `LoanStageService::buildWorkflowSnapshot()` picks new default automatically for loans created after the change
6. **In-flight loans** — frozen in their snapshot; either accept drift OR write a rebuild script and run in tinker
7. **Role resolution path** — no code change: `resolveStageRole()` → bank override → `Stage.default_role` → `task_owner` flow handles it
8. **User picker** — `GET /loans/{loan}/stages/{stageKey}/eligible-users?role=office_employee` returns the new pool
9. **Tests** — `php artisan test --compact --filter=RoleResolution`
10. **Docs** — `.docs/workflow-developer.md` + `.docs/user-assignment.md`

---

## Recipe 8 — Add a New Phase Action (Phase Transition Button)

**Use case:** Rate-PF needs a new "send_to_compliance" action between phases 2 and 3.

### Steps

1. **Update Stage.sub_actions** JSON with new phase + role
2. **Controller method** — `LoanStageController::ratePfAction()` — add `case 'send_to_compliance':`
   - Validate current phase matches
   - Call `LoanStageService::transferStage($loan, 'rate_pf', $nextUserId, $reason)`
   - Update notes: `rate_pf_phase = new phase number`, preserve `rate_pf_original_assignee`
   - Log activity
3. **View button** — add conditional action button in rate-pf partial, visible only when `rate_pf_phase == matching phase` AND current user matches phase role
4. **Route** — existing `POST /loans/{loan}/stages/rate-pf-action` dispatches by action name; no new route needed
5. **Tests** — simulate full multi-phase chain, assert each transfer + final completion
6. **Docs** — update the controller action endpoints table in `.docs/workflow-developer.md`

---

## Cross-Cutting Checklist (every workflow change)

- [ ] Update `Stage` master row / `sub_actions` via migration (not seed overrides)
- [ ] `LoanStageService::initializeStages()` contains the key
- [ ] `canStartStage()` has a branch if prerequisites differ from default
- [ ] `handleStageCompletion()` has a branch if post-completion behavior differs from default sequential advance
- [ ] `LoanStageController::getFieldErrors()` + `isStageDataComplete()` cover all required fields for that stage
- [ ] Blade partial exists and is wired into `loans/show.blade.php`
- [ ] `BankStageConfig` / `ProductStage` / `ProductStageUser` rows updated if defaults changed
- [ ] `workflow_config` snapshot impact considered (new loans only; document if in-flight drift is acceptable)
- [ ] Activity log + notifications fire
- [ ] Tests pass: `php artisan test --compact --filter=YourTest`
- [ ] Pint clean: `vendor/bin/pint --dirty --format agent`
- [ ] Docs updated: `.docs/workflow-developer.md`, `.docs/workflow-guide.md`, `.claude/services-reference.md`, `.claude/database-schema.md` (if schema touched), `.claude/routes-reference.md` (if routes touched)
- [ ] `tasks/todo.md` + `tasks/lessons.md` updated

---

## Common Pitfalls

- **Never write `stage_assignments.assigned_to` directly** — always call `LoanStageService::assignStage()` or `transferStage()`. They write the `stage_transfers` ledger and reassign open queries.
- **Completion blocked by queries** — pending/responded `StageQuery` rows block `updateStageStatus()`. UI shows count; resolve via `StageQueryService::resolveQuery()`.
- **Frozen snapshot** — `loan_details.workflow_config` JSON is set at creation. Admin reconfig never mutates in-flight loans. Rebuild script required if you want retroactive changes.
- **Parallel sub-stages** — set `parent_stage_key` AND `is_parallel=true` on both the master `Stage` row and the runtime `StageAssignment.is_parallel_stage` flag. Missing either breaks `checkParallelCompletion()`.
- **Soft revert cascades** — `revertStageIfIncomplete()` reverts the target AND all later pending stages. If you add stage-specific revert logic, hook into its per-stage callbacks; don't bypass.
- **Skip is permission-gated** — `skip_loan_stages` is disabled for all roles by default (migration `2026_04_13_124552`). Re-enable deliberately per role.
- **`EnsureUserIsActive` middleware** — deactivated assignees lose access; their assignments do not move automatically. Provide a transfer path.

---

## Where to Look First

| Symptom | First file to open |
|---|---|
| Stage doesn't open | `LoanStageService::handleStageCompletion()` + `canStartStage()` |
| Stage won't complete | `LoanStageController::getFieldErrors()` + `isStageDataComplete()` + pending queries |
| Wrong role assigned | `LoanStageService::resolveStageRole()` / `resolvePhaseRole()` + `buildWorkflowSnapshot` |
| Parallel parent never closes | `checkParallelCompletion()` + each child's status |
| Phase transition fails | Per-stage `*Action` method in `LoanStageController` + `Stage.sub_actions` |
| Next stage skipped incorrectly | `handleStageCompletion()` branch + `getNextStage()` sequence |
| Soft-revert breaking data | `revertStageIfIncomplete()` + `saveNotes` final block |

See `.docs/workflow-developer.md` for architecture, `.claude/services-reference.md` for method signatures.
