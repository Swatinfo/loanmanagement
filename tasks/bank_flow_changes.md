# Bank-Specific Phase Flow Changes (Rate & PF + Sanction Letter)

## Problem Statement

Currently, multi-phase stages (Rate & PF, Sanction Letter) always route Phase 2 to **Bank Employee**. This works for ICICI but for **Axis, Kotak, and HDFC** banks, Phase 2 should route to **Office Employee** instead.

### Current Flow (ICICI — correct)
- **Rate & PF:** Task Owner (fill details) → **Bank Employee** (review) → Task Owner (complete)
- **Sanction:** Task Owner (send) → **Bank Employee** (generate letter) → Task Owner (fill details)

### Desired New Flow (Axis, Kotak, HDFC)
- **Rate & PF:** Task Owner (fill details) → **Office Employee** (review) → Task Owner (complete)
- **Sanction:** Task Owner (send) → **Office Employee** (generate letter) → Task Owner (fill details)

All other stages remain unchanged. Subsequent stages (Docket, KFS, E-Sign, etc.) are unaffected.

---

## Root Cause Analysis

### 3 Bugs in Current Infrastructure

| # | What's broken | Where | Impact |
|---|---------------|-------|--------|
| 1 | **View reads roles from base only** | `workflow-product-stages.blade.php` line 363: `$saRoles = $subAction['roles']` — always reads from `stages.sub_actions`, ignores any `sub_actions_override` roles | All products show same role for each sub-action regardless of overrides |
| 2 | **Save doesn't preserve roles** | `WorkflowConfigController.php` line 139: `$saData['roles'] ?? []` — form has no role inputs, so roles save as `[]` | Any seeded override roles get wiped when admin saves product stage config |
| 3 | **Controller ignores sub-action config** | `LoanStageController.php`: `findBankEmployee()` is hardcoded bank_employee lookup, never reads product stage sub-action user config | Location user assignments configured in product stage config UI are dead config for phase 2 transfers |

### Existing Infrastructure (Already Built, Just Disconnected)

```
stages.sub_actions (base definition per stage):
  rate_pf:
    [0] bank_rate_details  → roles: ["bank_employee"]                              ← Phase 2
    [1] processing_charges → roles: ["loan_advisor", "branch_manager", "office_employee"]  ← Phase 1 & 3

  sanction:
    [0] send_for_sanction  → roles: ["loan_advisor", "branch_manager"], transfer_to_role: "bank_employee"
    [1] sanction_generated → roles: ["bank_employee"], transfer_to_role: "loan_advisor"
    [2] sanction_details   → roles: ["loan_advisor", "branch_manager"]

product_stages.sub_actions_override (per product, already exists as JSON column):
    [idx] → { is_enabled, roles[], location_overrides: [{location_id, users[], default}] }
```

The `sub_actions_override` column and UI already exist. The product stage config view already renders per-sub-action, per-location user assignment with checkboxes and default radio buttons. It just needs to be connected end-to-end.

---

## Solution Architecture

```
stages.sub_actions[idx].roles = ["bank_employee"]          ← DEFAULT for all products
                    ↓ (override?)
product_stages.sub_actions_override[idx].roles = ["office_employee"]  ← PER PRODUCT override
                    ↓ (resolve)
Effective roles = override roles if non-empty, else base roles
                    ↓ (find user)
product_stages.sub_actions_override[idx].location_overrides  ← PER LOCATION user assignment
                    ↓ (fallback)
Role-based fallback (any active user with that role in branch)
```

### Feature Flag

- `ALTERNATE_PHASE_FLOW=false` in `.env`
- `config('app.alternate_phase_flow')` — master switch
- When OFF: current behavior (`findBankEmployee()` for all products)
- When ON: reads from product sub-action config (resolves to OE for Axis/Kotak/HDFC, BE for ICICI based on their `sub_actions_override`)

---

## Implementation Plan

### Phase 1: ENV Flag + Config

**File: `.env`**
```
ALTERNATE_PHASE_FLOW=false
```

**File: `config/app.php`**
```php
'alternate_phase_flow' => env('ALTERNATE_PHASE_FLOW', false),
```

### Phase 2: Migration — Seed Override Roles for Axis/Kotak/HDFC

**New migration file.**

For each product belonging to Axis Bank, Kotak Mahindra Bank, and HDFC Bank:
- Find or create `product_stages` row for `rate_pf` stage
- Set `sub_actions_override[0].roles = ["office_employee"]` (Bank Rate Details phase)
- Find or create `product_stages` row for `sanction` stage
- Set `sub_actions_override[0].roles = ["office_employee"]` (send_for_sanction — transfer target)
- Set `sub_actions_override[1].roles = ["office_employee"]` (sanction_generated phase)

**Important:** Preserve any existing `is_enabled` and `location_overrides` data in the override. Merge, don't replace.

No changes to `stages.sub_actions` (base definitions stay as-is).
No new columns needed — `sub_actions_override` already exists.

### Phase 3: ProductStage Model — New Helper Methods

**File: `app/Models/ProductStage.php`**

Add two methods:

```php
/**
 * Get effective sub-action config: override merged with base.
 * Returns roles from override if non-empty, else from base sub_actions.
 */
public function getEffectiveSubAction(int $index): ?array
{
    $base = $this->stage->sub_actions[$index] ?? null;
    if (!$base) return null;

    $override = $this->sub_actions_override[$index] ?? [];

    return [
        ...$base,
        'is_enabled' => $override['is_enabled'] ?? true,
        'roles' => !empty($override['roles']) ? $override['roles'] : ($base['roles'] ?? []),
        'location_overrides' => $override['location_overrides'] ?? [],
    ];
}

/**
 * Find user for a sub-action phase based on location overrides.
 * Same hierarchy as main stage: city → state → fallback.
 */
public function getSubActionUserForLocation(int $subActionIndex, ?int $branchId, ?int $cityId, ?int $stateId): ?int
{
    $override = $this->sub_actions_override[$subActionIndex] ?? [];
    $locOverrides = $override['location_overrides'] ?? [];

    // City match
    if ($cityId) {
        foreach ($locOverrides as $lo) {
            if (($lo['location_id'] ?? null) == $cityId && !empty($lo['default'])) {
                return (int) $lo['default'];
            }
        }
    }
    // State match
    if ($stateId) {
        foreach ($locOverrides as $lo) {
            if (($lo['location_id'] ?? null) == $stateId && !empty($lo['default'])) {
                return (int) $lo['default'];
            }
        }
    }

    return null;
}
```

### Phase 4: Fix Product Stage Config View — Read Effective Roles

**File: `resources/views/settings/workflow-product-stages.blade.php`**

**Change 1 — Line 363:** Read override roles if available, else base roles.

```php
// BEFORE:
$saRoles = $subAction['roles'] ?? [];

// AFTER:
$saOverrideRoles = $psSubActions[$saIdx]['roles'] ?? [];
$saRoles = !empty($saOverrideRoles) ? $saOverrideRoles : ($subAction['roles'] ?? []);
```

**Change 2 — After the enable/disable toggle (around line 403):** Add hidden inputs so roles round-trip through form save.

```blade
@foreach ($saRoles as $r)
    <input type="hidden"
           name="stages[{{ $si }}][sub_actions_override][{{ $saIdx }}][roles][]"
           value="{{ $r }}">
@endforeach
```

**Change 3 — Line 390:** The role label display already uses `$saRoles`, so it will automatically show the correct role after Change 1.

**Change 4 — Phase description text (lines 126-136):** Make the `$psPhases` match for `rate_pf` and `sanction` dynamic — read the effective role from the product's sub-action config instead of hardcoding "Bank Employee".

```php
// For rate_pf, instead of hardcoded:
['phase' => '2', 'role' => 'Bank Employee', 'action' => 'Reviews/edits...'],

// Make it dynamic:
$ratePfPhase2Role = (!empty($psSubActions[0]['roles']) && in_array('office_employee', $psSubActions[0]['roles']))
    ? 'Office Employee' : 'Bank Employee';
// Then use $ratePfPhase2Role in the phase description
```

Same pattern for sanction.

**Change 5 — Transfer description (lines 173-174):** Make dynamic too.

```php
// Instead of hardcoded:
'rate_pf' => 'Auto-transfers: Task Owner → Bank Employee → Task Owner',

// Dynamic:
'rate_pf' => 'Auto-transfers: Task Owner → ' . $ratePfPhase2Role . ' → Task Owner',
```

### Phase 5: Fix Save Controller — Roles Already Handled

**File: `app/Http/Controllers/WorkflowConfigController.php`**

Line 160 already does:
```php
'roles' => array_values(array_intersect($saRoles, Role::pluck('slug')->toArray())),
```

With the hidden inputs from Phase 4 Change 2, `$saData['roles']` will now contain the actual roles. **No controller change needed** — the save already works correctly once the form submits roles.

### Phase 6: Controller — New Phase Assignee Method

**File: `app/Http/Controllers/LoanStageController.php`**

Add new private method:

```php
/**
 * Find the best assignee for a sub-action phase using product stage config.
 * Reads sub_actions_override location_overrides, falls back to role-based lookup.
 */
private function findPhaseAssignee(LoanDetail $loan, string $stageKey, int $subActionIndex): ?int
{
    $stage = \App\Models\Stage::where('stage_key', $stageKey)->first();
    if (!$stage) return null;

    $ps = \App\Models\ProductStage::where('product_id', $loan->product_id)
                                  ->where('stage_id', $stage->id)->first();
    if (!$ps) return null;

    // Resolve location from branch
    $branch = $loan->branch_id ? \App\Models\Branch::with('location.parent')->find($loan->branch_id) : null;
    $cityId = $branch?->location_id;
    $stateId = $branch?->location?->parent_id;

    // Check sub-action location overrides first
    $userId = $ps->getSubActionUserForLocation($subActionIndex, $loan->branch_id, $cityId, $stateId);
    if ($userId) {
        $user = \App\Models\User::where('id', $userId)->where('is_active', true)->first();
        if ($user) return $user->id;
    }

    // Fallback: get effective role and find any matching user in branch
    $effective = $ps->getEffectiveSubAction($subActionIndex);
    $roles = $effective['roles'] ?? [];
    if (!empty($roles) && $loan->branch_id) {
        $user = \App\Models\User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->whereIn('slug', $roles))
            ->whereHas('branches', fn($q) => $q->where('branches.id', $loan->branch_id))
            ->first();
        if ($user) return $user->id;
    }

    // Last fallback: any active user with the role
    if (!empty($roles)) {
        $user = \App\Models\User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->whereIn('slug', $roles))
            ->first();
        if ($user) return $user->id;
    }

    return null;
}
```

### Phase 7: Controller — Modify `ratePfAction()` + `sanctionAction()`

**File: `app/Http/Controllers/LoanStageController.php`**

**`ratePfAction()` — `send_to_bank` action (around line 506-532):**

```php
if ($validated['action'] === 'send_to_bank') {
    // ... existing snapshot logic stays the same ...

    $assignment->mergeNotesData([
        'rate_pf_phase' => '2',
        'rate_pf_original_assignee' => $assignment->assigned_to,
        'original_values' => $originalValues,
    ]);

    if (config('app.alternate_phase_flow')) {
        // Use product sub-action config (resolves to OE for Axis/Kotak/HDFC, BE for ICICI)
        $targetId = $validated['transfer_to'] ?? $this->findPhaseAssignee($loan, 'rate_pf', 0);
    } else {
        // Legacy behavior
        $targetId = $validated['transfer_to'] ?? $loan->assigned_bank_employee;
        if (!$targetId) $targetId = $this->findBankEmployee($loan);
    }

    if ($targetId) {
        $this->stageService->transferStage($loan, 'rate_pf', (int) $targetId, 'Sent for rate review');
    }

    return response()->json(['success' => true, 'message' => 'Sent for review']);
}
```

**`sanctionAction()` — `send_for_sanction` action (around line 253-268):**

```php
if ($validated['action'] === 'send_for_sanction') {
    $assignment->mergeNotesData([
        'sanction_phase' => '2',
        'sanction_original_assignee' => $assignment->assigned_to,
    ]);

    if (config('app.alternate_phase_flow')) {
        $targetId = $validated['transfer_to'] ?? $this->findPhaseAssignee($loan, 'sanction', 0);
    } else {
        $targetId = $validated['transfer_to'] ?? $loan->assigned_bank_employee;
        if (!$targetId) $targetId = $this->findBankEmployee($loan);
    }

    if ($targetId) {
        $this->stageService->transferStage($loan, 'sanction', (int) $targetId, 'Sent for sanction letter generation');
    }

    return response()->json(['success' => true, 'message' => 'Sent for sanction letter generation']);
}
```

**`sanctionAction()` — `sanction_generated` action (around line 271-291):**

The return-to-owner logic (phase 2 → phase 3) needs similar treatment. Currently it checks `hasRole('bank_employee')` to avoid returning to a BE. With alternate flow, the phase 2 user might be an OE.

```php
if ($validated['action'] === 'sanction_generated') {
    $notesData = $assignment->getNotesData();
    $assignment->mergeNotesData(['sanction_phase' => '3']);

    $transferTo = $validated['transfer_to'] ?? null;
    if (!$transferTo) {
        $originalAssignee = $notesData['sanction_original_assignee'] ?? null;
        if ($originalAssignee) {
            $origUser = User::find($originalAssignee);
            if (config('app.alternate_phase_flow')) {
                // In alternate flow, phase 2 user is OE — check if original is NOT OE
                if ($origUser && $origUser->hasRole('office_employee')) {
                    $originalAssignee = null;
                }
            } else {
                // Legacy: check if original is NOT BE
                if ($origUser && $origUser->hasRole('bank_employee')) {
                    $originalAssignee = null;
                }
            }
        }
        $transferTo = $originalAssignee ?? $loan->created_by;
    }
    if ($transferTo) {
        $this->stageService->transferStage($loan, 'sanction', (int) $transferTo, 'Sanction letter generated');
    }

    return response()->json(['success' => true, 'message' => 'Sanction letter marked as generated']);
}
```

### Phase 8: View — Conditional Phase Labels in Stages View

**File: `resources/views/loans/stages.blade.php`**

**Change 1 — Phase pills (around line 466-482):**

Add a helper to resolve the phase 2 role label:

```php
@php
    $getPhase2Role = function($stageKey, $subActionIdx) use ($loan) {
        if (!config('app.alternate_phase_flow')) return null; // use defaults
        $stage = \App\Models\Stage::where('stage_key', $stageKey)->first();
        if (!$stage) return null;
        $ps = \App\Models\ProductStage::where('product_id', $loan->product_id)
            ->where('stage_id', $stage->id)->first();
        if (!$ps) return null;
        $effective = $ps->getEffectiveSubAction($subActionIdx);
        $roles = $effective['roles'] ?? [];
        if (in_array('office_employee', $roles)) return 'Office Employee';
        return null; // use default
    };

    $ratePfPhase2Label = $getPhase2Role('rate_pf', 0) ?? 'Bank Employee';
    $sanctionPhase2Label = $getPhase2Role('sanction', 0) ?? 'Bank Employee';
@endphp
```

Then in the phase config match:

```php
'rate_pf' => [
    'current' => $assignment->getNotesData()['rate_pf_phase'] ?? '1',
    'phases' => [
        ['key' => '1', 'label' => 'Fill Details', 'role' => 'Loan Advisor'],
        ['key' => '2', 'label' => ($ratePfPhase2Label === 'Office Employee' ? 'Office Review' : 'Bank Review'), 'role' => $ratePfPhase2Label],
        ['key' => '3', 'label' => 'Final Review', 'role' => 'Loan Advisor'],
    ],
],
'sanction' => [
    'current' => $assignment->getNotesData()['sanction_phase'] ?? '1',
    'phases' => [
        ['key' => '1', 'label' => 'Send to ' . ($sanctionPhase2Label === 'Office Employee' ? 'Office' : 'Bank'), 'role' => 'Task Owner'],
        ['key' => '2', 'label' => 'Generate Letter', 'role' => $sanctionPhase2Label],
        ['key' => '3', 'label' => 'Fill Details', 'role' => 'Task Owner'],
    ],
],
```

**Change 2 — Rate & PF Phase 1 dropdown (around line 1920-1928):**

```blade
@php
    $ratePfDropdownRole = $ratePfPhase2Label === 'Office Employee' ? 'office_employee' : 'bank_employee';
    $ratePfDropdownLabel = $ratePfPhase2Label === 'Office Employee' ? 'Select Office Employee...' : 'Select Bank Employee...';
@endphp
<select class="shf-input shf-input-sm shf-transfer-user"
    data-stage="rate_pf" data-role="{{ $ratePfDropdownRole }}"
    data-loan-id="{{ $loan->id }}" style="max-width:220px">
    <option value="">{{ $ratePfDropdownLabel }}</option>
</select>
<button class="btn-accent-sm shf-rate-pf-action"
    data-loan-id="{{ $loan->id }}" data-action="send_to_bank">
    Send for Review
</button>
```

**Change 3 — Sanction Phase 1 dropdown (around line 2410-2416):**

Same pattern:
```blade
@php
    $sanctionDropdownRole = $sanctionPhase2Label === 'Office Employee' ? 'office_employee' : 'bank_employee';
    $sanctionDropdownLabel = $sanctionPhase2Label === 'Office Employee' ? 'Select Office Employee...' : 'Select Bank Employee...';
@endphp
<select class="shf-input shf-input-sm shf-transfer-user"
    data-stage="sanction" data-role="{{ $sanctionDropdownRole }}"
    data-loan-id="{{ $loan->id }}" style="max-width:220px">
    <option value="">{{ $sanctionDropdownLabel }}</option>
</select>
```

**Change 4 — Phase 2 info text (optional cosmetic):**

Rate & PF Phase 2 (line ~1935): Change "Rate request received" alert text — works for both roles, no change needed.

Sanction Phase 2 (line ~2423): "Waiting for sanction letter" — works for both roles, no change needed.

---

## File Change Summary

| # | File | Change Type | Description |
|---|------|-------------|-------------|
| 1 | `.env` | Add | `ALTERNATE_PHASE_FLOW=false` |
| 2 | `config/app.php` | Edit | Register `alternate_phase_flow` config key |
| 3 | New migration | Create | Seed `sub_actions_override` roles for Axis/Kotak/HDFC products |
| 4 | `app/Models/ProductStage.php` | Edit | Add `getEffectiveSubAction()` + `getSubActionUserForLocation()` |
| 5 | `resources/views/settings/workflow-product-stages.blade.php` | Edit | Read effective roles (override > base) + add hidden role inputs |
| 6 | `app/Http/Controllers/LoanStageController.php` | Edit | Add `findPhaseAssignee()` + if/else in `ratePfAction()` + `sanctionAction()` |
| 7 | `resources/views/loans/stages.blade.php` | Edit | Conditional phase pills, dropdown role, labels (~4 spots) |

No changes to: `WorkflowConfigController.php` (save already works), `stages` table, `stages.sub_actions`, main stage `default_role`.

---

## How Product Stage Config UI Will Look After Changes

### ICICI Bank / Home Loan (unchanged):
```
Rate & PF                                              [✓ enabled]
  ⤷ Bank Rate Details    [Form] (Bank Employee)        [✓]
      Gujarat/Rajkot    [✓ Ramesh (BE)]  (● default)
  ⤷ Processing & Charges [Form] (LA, BM, OE)           [✓]
      Gujarat/Rajkot    [✓ Suresh (OE)]  (● default)

Sanction Letter                                         [✓ enabled]
  ⤷ Send for Sanction   [Action] (LA, BM)              [✓]
  ⤷ Sanction Generated  [Action] (Bank Employee)       [✓]
      Gujarat/Rajkot    [✓ Ramesh (BE)]  (● default)
  ⤷ Sanction Details    [Form] (LA, BM)                [✓]
```

### Axis Bank / Home Loan (after migration seeds override):
```
Rate & PF                                              [✓ enabled]
  ⤷ Bank Rate Details    [Form] (Office Employee)      [✓]     ← role overridden
      Gujarat/Rajkot    [✓ Jayesh (OE)]  (● default)           ← shows OE users
  ⤷ Processing & Charges [Form] (LA, BM, OE)           [✓]
      Gujarat/Rajkot    [✓ Jayesh (OE)]  (● default)

Sanction Letter                                         [✓ enabled]
  ⤷ Send for Sanction   [Action] (LA, BM)              [✓]
  ⤷ Sanction Generated  [Action] (Office Employee)     [✓]     ← role overridden
      Gujarat/Rajkot    [✓ Jayesh (OE)]  (● default)           ← shows OE users
  ⤷ Sanction Details    [Form] (LA, BM)                [✓]
```

---

## Runtime Assignment Resolution (Phase 2 Transfer)

When flag is ON and task owner clicks "Send for Review":

| Priority | Source | Lookup |
|----------|--------|--------|
| 1st | Manual selection | User picked someone from dropdown |
| 2nd | Product sub-action location config | `sub_actions_override[0].location_overrides` → city match → state match |
| 3rd | Role + branch fallback | Any active user with effective role in loan's branch |
| 4th | Role fallback | Any active user with effective role (any branch) |

When flag is OFF: current `findBankEmployee()` behavior (unchanged).

---

## Edge Cases & Considerations

| Concern | Answer |
|---------|--------|
| **Existing loans mid-phase** | Loans already in phase 2 with a bank_employee assigned won't change. Flag only affects new phase transitions. |
| **Save overwrites override** | Fixed by adding hidden role inputs — roles now round-trip through form. |
| **Different OE per stage** | Each stage's `sub_actions_override` is independent — rate_pf and sanction can have different OEs per location. |
| **Different OE per location** | Location overrides within sub-action config handle this (city → state hierarchy). |
| **New product added later** | Admin needs to configure sub-action roles in product stage config. Without override, defaults to base roles (bank_employee). |
| **Main stage config untouched** | `stages.default_role` and `stages.sub_actions` unchanged. |
| **ICICI unaffected** | No override seeded for ICICI products — uses base roles. |
| **Future: UI to change roles** | Could add a role dropdown per sub-action in product stage config (not needed now — migration seeds it, hidden inputs preserve it). |

---

## Testing Checklist

- [ ] Set `ALTERNATE_PHASE_FLOW=false` — verify all flows work exactly as today (no regression)
- [ ] Set `ALTERNATE_PHASE_FLOW=true`
- [ ] Create loan with ICICI product — verify Rate & PF phase 2 goes to Bank Employee
- [ ] Create loan with Axis product — verify Rate & PF phase 2 goes to Office Employee
- [ ] Verify Sanction phase 2 follows same pattern per bank
- [ ] Open product stage config for Axis — verify sub-action shows "Office Employee" role and OE users
- [ ] Save product stage config for Axis — verify roles are preserved (not wiped)
- [ ] Open product stage config for ICICI — verify sub-action still shows "Bank Employee"
- [ ] Verify phase pills on loan stages page show correct role label per bank
- [ ] Verify phase 1 dropdown shows correct role users (OE for Axis, BE for ICICI)
- [ ] Test with no product stage config at all — verify fallback works
