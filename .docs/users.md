# Users

User management, role assignment, branch/bank/location scoping, impersonation.

## Surfaces

- `/users` — list (DataTable, AJAX)
- `/users/create`, `/users/{id}/edit` — create/edit with permissions panel
- `/users/check-email` — AJAX uniqueness check
- `/users/product-stage-holders` — AJAX: current default users per product+stage (for reassignment UI)
- `/users/{id}/toggle-active` — POST flip `is_active`
- `/impersonate/take/{id}`, `/impersonate/leave`, `/api/impersonate/users` — impersonation

Routes + permissions: `.claude/routes-reference.md`. Model: `.claude/database-schema.md`.

## Create / edit flow (`UserController@store|update`)

Validation (inline):

- `name` — required
- `email` — required, email, unique (ignore self on update)
- `password` — required on create, confirmed, min:8; optional on update
- `phone` — nullable
- `is_active` — boolean
- `roles[]` — required array, min:1 (by slug)
- `default_branch_id` — nullable exists
- `assigned_banks[]` — nullable exists

Operations (on save):

1. Create/update `User` record (password cast auto-hashes).
2. `roles()->sync()` via slug → ID mapping.
3. `syncUserBranches()` — multi-branch assignment + per-branch OE default flags (clears prior OE defaults).
4. `syncBankAssignments()` — `bank_employees` pivot for `bank_employee` / `office_employee` roles, with city-level defaults.
5. `replaceProductStageUsers()` — bulk-swaps old user with new in `product_stage_users` (per product).
6. Syncs `location_user` pivot.
7. `syncUserPermissions()` — writes `user_permissions` grant/deny rows.
8. Logs activity.
9. Clears `PermissionService` user cache.

## Delete

`UserController@destroy`:
- Cannot self-delete
- Cannot delete `super_admin` unless you are `super_admin`
- Cannot delete a user that has loans (blocked, returns error JSON)
- Soft-deletes the user (only if `SoftDeletes` trait is present — `User` does **not** currently use soft deletes; check before relying)

## Toggle active

`UserController@toggleActive`:
- Cannot toggle self
- Cannot toggle `super_admin` (unless you are)
- Flips `is_active`, logs activity (`user_activated` / `user_deactivated`)
- Because `EnsureUserIsActive` is globally active, deactivated users lose access on their next request.

## User visibility of users

There's no hard scope filter on the users list — anyone with `view_users` sees all users. Per-row **action visibility** depends on permissions:

- Edit link → `edit_users`
- Toggle active → `edit_users` (+ not self, + not super_admin if not super_admin)
- Delete → `delete_users` (+ not self, + not super_admin if not super_admin)

## Data tables & filters

`GET /users/data` (DataTables server-side):

- Filters: `role` (slug), `status` (active/inactive)
- Search: name, email, phone
- Ordering: default `created_at desc`
- Eager loads: roles, branches, employerBanks, locations, product assignments (grouped by bank)

## Impersonation

### Authorization

- `User::canImpersonate()` → super_admin OR `app.allow_impersonate_all=true` (env: `ALLOW_IMPERSONATE_ALL`)
- `User::canBeImpersonated()` → not super_admin

### Endpoints

- `GET /api/impersonate/users?search=...` — user picker dropdown. Returns active, non-super_admin users. Excludes current user.
- `GET /impersonate/take/{id}` — sets impersonation session. Smart redirect: tries referrer, falls back to dashboard if impersonated user lacks access via `canAccessPath()`.
- `GET /impersonate/leave` — restores original user, redirects smartly.

### UI

- Layout (`layouts/app.blade.php`) renders an **impersonation banner** (`.shf-impersonation-banner`) when impersonation is active, with a "Leave impersonation" link.
- Navbar/header has a search modal gated by `canImpersonate()` (uses SweetAlert2 confirmation).

## User–branch / bank / location relationships

`User` model relationships:

| Relationship | Purpose |
|---|---|
| `branches()` | `user_branches` pivot — multi-branch assignment. `is_default_office_employee` per branch |
| `defaultBranch()` | `default_branch_id` on user row — default for task/loan creation scope |
| `taskBank()` | `task_bank_id` — default bank for "new task" on dashboard |
| `employerBanks()` | `bank_employees` pivot — for bank_employee / office_employee role users, with `is_default` + `location_id` |
| `locations()` | `location_user` pivot — serviceable cities |

These all power the **auto-assignment** resolution in `LoanStageService::findBestAssignee()`. See `user-assignment.md`.

## Activity & audit

`HasAuditColumns` trait (used on many models) automatically sets `updated_by` / `deleted_by` from `auth()->id()`. `User` itself uses `created_by` set in the controller.

## Testing caveats

The default Breeze profile tests (`ProfileTest.php`) fail because `EnsureUserIsActive` middleware doesn't play well with the test's fake user fixtures, and the Breeze registration tests fail because registration routes are disabled. Known, pre-existing — do not debug during unrelated work.
