# Permissions

Permission-based access control. 45 permissions across 7 groups, resolved through `PermissionService`.

## Where things live

- **Catalog**: `config/permissions.php` — source-of-truth slug list grouped by area
- **Seeded into**: `permissions` table (seeded by `DefaultDataSeeder` + migrations)
- **Role mapping**: `role_permission` pivot (see `roles.md`)
- **User overrides**: `user_permissions` (grant / deny per user)
- **Middleware**: `permission:slug` on routes (alias for `CheckPermission`)
- **Resolver**: `PermissionService::userHasPermission(User, slug)`

## Permission groups

| Group | Count | Slugs |
|---|---|---|
| **Settings** | 8 | view_settings, edit_company_info, edit_banks, edit_documents, edit_tenures, edit_charges, edit_services, edit_gst |
| **Quotations** | 8 | create_quotation, generate_pdf, view_own_quotations, view_all_quotations, delete_quotations, download_pdf, download_pdf_branded, download_pdf_plain |
| **Users** | 5 | view_users, create_users, edit_users, delete_users, assign_roles |
| **Loans** | 14 | convert_to_loan, view_loans, view_all_loans, create_loan, edit_loan, delete_loan, manage_loan_documents, upload_loan_documents, download_loan_documents, delete_loan_files, manage_loan_stages, skip_loan_stages, add_remarks, manage_workflow_config |
| **Tasks** | 1 | view_all_tasks |
| **DVR** | 5 | view_dvr, create_dvr, edit_dvr, delete_dvr, view_all_dvr |
| **System** | 4 | change_own_password, manage_permissions, view_activity_log, view_reports |

Additional slugs live in migrations (beyond `config/permissions.php`): `manage_customers`, `view_customers`, `impersonate_users`, `view_dashboard`, `manage_notifications`, `transfer_loan_stages`, `reject_loan`, `change_loan_status`, `view_loan_timeline`, `manage_disbursement`, `manage_valuation`, `raise_query`, `resolve_query`.

## Resolution flow

```
User::hasPermission($slug)
  └─ PermissionService::userHasPermission()
     1. super_admin? → true  (bypass)
     2. user_permissions.type for ($user, $slug)? → grant/deny
     3. any role in role_user → any role_permission row? → true
     4. else → false
```

- Super admin bypass is absolute.
- Explicit `deny` in `user_permissions` overrides role defaults.
- If no user override, role defaults win (any role granting is sufficient).

## Caching

All three lookups use `Cache::remember(..., 300, ...)` (5 minutes):

| Key | Value |
|---|---|
| `user_perms:{userId}` | `[slug => 'grant'|'deny']` — just user overrides |
| `user_role_ids:{userId}` | array of role IDs |
| `role_perms:{sortedRoleIds}` | unique permission slugs across those roles |

### Invalidation

Clear caches after writes:

- `PermissionService::clearUserCache($user)` — after user's role list or user-permission overrides change
- `PermissionService::clearRoleCache()` — after any role_permission write
- `PermissionService::clearAllCaches()` — after bulk role edits, new permission created, etc.

Controllers that mutate permissions already call the correct clear methods (`PermissionController@update`, `UserController@store/update`, `RoleManagementController@store/update`, `LoanSettingsController@saveTaskRolePermissions`).

## Usage patterns

### Route middleware

```php
Route::middleware(['auth', 'permission:create_loan'])->post('/loans', ...);
```

Two permissions → user needs **both**:

```php
Route::middleware(['auth', 'permission:manage_loan_stages', 'permission:transfer_loan_stages'])
  ->post('/loans/{loan}/stages/{stageKey}/transfer', ...);
```

### In controllers

```php
if (! $request->user()->hasPermission('view_all_loans')) { abort(403); }
```

### In Blade

```blade
@if(auth()->user()->hasPermission('create_quotation'))
  <a href="{{ route('quotations.create') }}" class="btn-accent">New Quotation</a>
@endif
```

## Managing permissions in the UI

- **Permissions page** (`/permissions`, `manage_permissions` required): role × permission matrix for non-Loans groups. Loans permissions are managed under Loan Settings for role-scoping.
- **Loan Settings → Role Permissions** (`/loan-settings` tab): Loans-group permissions per role.
- **User edit page** (`/users/{id}/edit`): per-user grant/deny overrides appear alongside role permissions.

All three call `PermissionService::clearAllCaches()` (or more targeted) after save.

## Common pitfalls

- **Don't** add permission-check calls inside `Cache::remember` wrappers — you'll end up with nested caches and tricky invalidation.
- **Don't** assume `null` from `getUserOverride()` means deny. It means "no override set"; fall through to role lookup.
- **Don't** hardcode permission slugs in multiple places — reference `config('permissions')` when enumerating.
- Migrations that add/remove permissions must also update any role_permission seeder or fixture.

## See also

- `.claude/database-schema.md` — `permissions`, `user_permissions`, `role_permission` tables
- `.claude/services-reference.md` — `PermissionService` full method list
- `roles.md` — role → default permission mappings
