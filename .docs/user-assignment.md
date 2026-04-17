# User Assignment

How users get auto-assigned to loan stages. Core logic is in `LoanStageService`.

## Problem

Each stage of a loan has an **assigned role** (e.g., `bank_employee` for Rate & PF). When the stage unlocks, we need to pick a **specific user** — by bank, branch, location, and product defaults.

## Two-phase design

### 1. Snapshot at loan creation

When a loan is created (quotation conversion or direct), `LoanStageService::buildWorkflowSnapshot()` freezes a complete role + default-user map into `loan_details.workflow_config`:

```json
{
  "document_collection": { "role": "loan_advisor", "default_user_id": 42, "phases": {} },
  "rate_pf": {
    "role": "bank_employee",
    "default_user_id": 7,
    "phases": {
      "0": { "role": "bank_employee", "default_user_id": 7 },
      "1": { "role": "office_employee", "default_user_id": 19 }
    }
  },
  ...
}
```

This protects in-flight loans from later role config changes. Admins can reshuffle role defaults without reassigning everyone's stages.

### 2. Resolution at assignment time

`findUserForRole(role, loan, stageKey, ?phaseIndex)` priority:

1. **Snapshot default_user** (if set at creation)
2. **Role-specific resolution** for snapshot misses:
   - `task_owner` → `loan.assigned_advisor` or `loan.created_by`
   - `bank_employee` → `findBankEmployeeForLoan()`
   - `office_employee` → `findOfficeEmployeeForLoan()`
3. Fallback: null (stage shows as assignable but unassigned)

## Role resolution — which role for a stage?

`resolveStageRole(stageKey, bankId)` priority:

1. `bank_stage_configs.assigned_role` for (bank, stage) — bank-specific override
2. `stages.assigned_role` — master default
3. `'task_owner'` — catch-all

`resolvePhaseRole(stageKey, phaseIndex, bankId)` priority:

1. `bank_stage_configs.phase_roles[phaseIndex]`
2. `stages.sub_actions[phaseIndex].role`
3. `'task_owner'`

## User resolution — which specific user?

### Task owner / advisor-eligible

Uses `loan.assigned_advisor` (set at creation) or `loan.created_by`. The "SHF" role suffix in the UI.

### Bank employee (`findBankEmployeeForLoan`)

Priority:
1. **Product stage specific**: `product_stage_users` row matching (product_stage, branch, location, phase_index) — uses `ProductStage::getUserForLocation()` walking the branch → city → state → global hierarchy
2. **Bank default for loan's city**: `bank_employees` where `bank_id` matches loan + `location_id` = loan's city + `is_default=true`
3. **Any active bank employee for bank**: first match by bank
4. null

### Office employee (`findOfficeEmployeeForLoan`)

Priority:
1. `product_stage_users` match (same hierarchy as bank employee)
2. `user_branches` where `branch_id` = loan branch + `is_default_office_employee=true`
3. Any OE assigned to the branch
4. Any active OE globally

## Product stage assignments

Admins configure detailed user assignments at **Loan Settings → Product → Stages**. The form can target:

- **Stage-level default** — one `default_user_id` on `product_stages` row
- **Branch-specific** — `product_stage_users` with `branch_id` set
- **Location-specific** — with `location_id` (city) set (hierarchical: branch → city → state → global default)
- **Phase-specific** — with `phase_index` set (for multi-phase stages)

Resolution prefers the most-specific match and walks up the location hierarchy if no branch-specific assignment exists.

## Transfer (manual reassignment)

`LoanStageService::transferStage(loan, stageKey, toUserId, reason)`:

1. Updates `stage_assignments.assigned_to`
2. Creates `stage_transfers` ledger entry (type=`manual`)
3. Reassigns all **pending/responded** queries on the stage to the new assignee's stage_assignment_id
4. Logs activity
5. Touches `loan.updated_at` so the list reorders

Transfer is on the stage-update action routes (`POST /loans/{loan}/stages/{stageKey}/transfer`), requires both `manage_loan_stages` and `transfer_loan_stages` permissions.

## Stage-specific multi-phase transfers

Several stages use transfer to hand off between phases:

- `sanction_action` — Phase 1 (office prep) → Phase 2 (bank generation) → Phase 3 (completion)
- `legal_action` — similar 3-phase ping-pong
- `technical_valuation_action` — Phase 1 (office) → Phase 2 (valuation)
- `docket_action` — Phase 1 (bank submission) → Phase 2 (office review)
- `rate_pf_action` — Phase 1 (owner fills) → Phase 2 (bank review) → Phase 3 (owner completes)
- `esign_action` — 4-phase chain (bank → customer → office → owner)

Each action endpoint reads current phase from `stage_assignments.notes`, does the transfer, writes the new phase back to notes. `LoanStageController` orchestrates — see the controller methods named after the actions.

## Why this matters in practice

- **Never hardcode** users to stages. Always go through the snapshot or resolver.
- **After changing role/user defaults**: existing loans keep their snapshot until a transfer or re-init. If you need to migrate a batch, build a console command that rebuilds `workflow_config` for affected loans.
- **When a user is deactivated**: their assigned stages are not automatically reassigned. A BM / BDH must transfer each. Adding an auto-reassignment on `is_active=false` would be a reasonable feature to add in the future.

## See also

- `.claude/services-reference.md` — `LoanStageService` full API
- `.claude/database-schema.md` — `product_stage_users`, `bank_employees`, `user_branches`, `bank_stage_configs`, `stage_transfers`
- `workflow-developer.md` — stage lifecycle + multi-phase details
- `roles.md` — advisor-eligible flag and role semantics
