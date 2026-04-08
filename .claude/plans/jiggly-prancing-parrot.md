# Plan: Permission-Based Visibility & Dual Role System Fix

## Context
Two role systems exist:
- **System roles** (super_admin, admin, staff) → control Settings, Quotations, Users, System permissions
- **Task roles** (branch_manager, loan_advisor, bank_employee, office_employee) → control Loans permissions only, additive to system role

**Problems:**
1. `/permissions` page shows Loans group permissions — but Loans permissions are managed via task roles at `/loan-settings#role-permissions`. Showing them on both pages is confusing.
2. Dashboard shows quotation stats/tabs to users who have no quotation permissions (e.g., bank_employee)
3. Need all menus, actions, and statistics to respect the combined permission result

## Permission Resolution (already correct in code)
`super_admin bypass → user override (grant/deny) → system role default → task role additive`

A staff user with task_role=bank_employee gets: staff defaults + bank_employee task permissions.

## Changes

### 1. `/permissions` page — Exclude Loans group
**Files:** `app/Http/Controllers/PermissionController.php`, `resources/views/permissions/index.blade.php`

In the controller `index()` method, filter out the Loans group:
```php
$permissions = Permission::where('group', '!=', 'Loans')->get()->groupBy('group');
```

In the `update()` method, only process non-Loans permissions:
```php
$allPermissions = Permission::where('group', '!=', 'Loans')->pluck('id')->toArray();
```

But keep super_admin getting ALL permissions (including Loans) since super_admin bypasses anyway.

Add a note in the view: "Loan permissions are managed separately in Loan Settings → Role Permissions"

### 2. Dashboard — Gate quotation sections by permission
**Files:** `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php`

**Controller changes:**
- Add `$canViewQuotations` flag:
  ```php
  $canViewQuotations = $user->hasPermission('create_quotation') 
      || $user->hasPermission('view_own_quotations') 
      || $user->hasPermission('view_all_quotations');
  ```
- Only build quotation stats, permissions array, and users list when `$canViewQuotations` is true
- Pass `$canViewQuotations` to view

**View changes:**
- Wrap quotation stat cards in `@if($canViewQuotations)`
- Wrap "Quotations" tab button in `@if($canViewQuotations)`
- Wrap quotation tab content in `@if($canViewQuotations)`
- Adjust stat card column widths based on what's visible
- Default tab: if no quotation access, default to first available tab (My Tasks or Loans)
- If user has neither quotation nor loan access, show a welcome/empty state

### 3. Navigation — Fix Loan Settings visibility
**File:** `resources/views/layouts/navigation.blade.php`

Currently all nav items are permission-gated, but Loan Settings uses `view_loans` which is too broad — bank employees can view loans but shouldn't manage settings.

Changes needed:
- **Loan Settings**: Change from `view_loans` to `manage_workflow_config` (desktop line 70 + mobile line 207)
- **Quotation Settings**: Change from `view_settings` to check for any settings edit permission (e.g. `view_settings` OR one of the edit_* permissions). Alternatively, keep `view_settings` but ensure it's only granted to admin/super_admin who should manage settings.

Actually, `view_settings` is already admin-only in role_defaults (staff doesn't have it). So the real fix may be ensuring the seeder/role_permissions are correct. Will verify during implementation.

Other nav items already correct:
- Dashboard → always visible ✓
- New Quotation → `create_quotation` ✓
- Loans → `view_loans` ✓
- Users → `view_users` ✓
- Activity Log → `view_activity_log` ✓

### 4. Statistics data scope — Already correct (no changes needed)
- Quotation stats: scoped to user's own if no `view_all_quotations`
- Loan stats: uses `LoanDetail::visibleTo($user)` scope
- Admin/super_admin see all data

## Files to Modify
1. `app/Http/Controllers/PermissionController.php` — Filter out Loans group
2. `resources/views/permissions/index.blade.php` — Add note about Loans permissions location
3. `app/Http/Controllers/DashboardController.php` — Add `$canViewQuotations` flag
4. `resources/views/dashboard.blade.php` — Gate quotation sections
5. `resources/views/layouts/navigation.blade.php` — Fix Loan Settings gate (desktop + mobile)

## Verification
1. **Bank employee** (staff + bank_employee): Dashboard shows only loan stats + My Tasks/Loans tabs. No quotation cards or tab. Nav shows Dashboard, Loans, Loan Settings only.
2. **Staff** (no task role): Dashboard shows quotation stats (own) + loan stats. Nav shows New Quotation, Loans, etc.
3. **Admin**: Full dashboard, all nav items.
4. **Super admin**: Everything.
5. `/permissions` page shows Settings, Quotations, Users, System groups only — no Loans group. Note directs to Loan Settings for loan permissions.
