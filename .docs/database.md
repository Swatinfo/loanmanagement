# Database

SQLite via Laravel 12 migrations. 76 migrations produce 38 application tables plus the standard Laravel framework tables.

The authoritative per-column reference is `.claude/database-schema.md`. This file covers conventions, migration practice, and domain groupings.

## Stack

- **Engine**: SQLite (`database/database.sqlite`)
- **Migrations**: `database/migrations/*.php` — Laravel 12 schema builder
- **Seeders**: `database/seeders/` — `DatabaseSeeder` → `DefaultDataSeeder` (+ `PermissionSeeder`)
- **Factories**: only `UserFactory` — others created only when explicitly needed

## Running

```bash
php artisan migrate           # run pending migrations
php artisan migrate:fresh --seed  # wipe + recreate + seed defaults
php artisan db:seed --class=DefaultDataSeeder
```

**Do not** create factories or seeders opportunistically. Only on explicit request.

## Domain groupings

### Auth & access

`users`, `roles`, `role_user` (pivot), `permissions`, `role_permission` (pivot), `user_permissions`, `user_branches`, `password_reset_tokens`, `sessions`.

### Geography / org

`locations` (self-referential state↔city), `branches`, `location_user`, `location_product`, `bank_location`.

### Banks & products

`banks`, `bank_charges`, `bank_employees`, `products`, `bank_stage_configs`.

### Workflow

`stages`, `product_stages`, `product_stage_users`.

### Customers & quotations

`customers`, `quotations`, `quotation_banks`, `quotation_emi` (singular), `quotation_documents`.

### Loans & stages

`loan_details`, `loan_documents`, `loan_progress`, `stage_assignments`, `stage_transfers`, `stage_queries`, `query_responses`, `remarks`, `valuation_details`, `disbursement_details`.

### Support

`shf_notifications`, `activity_logs`, `app_config`, `app_settings`, `general_tasks`, `general_task_comments`, `daily_visit_reports`, `task_role_permissions` (legacy).

### Laravel framework

`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`.

## Conventions

- **FK onDelete** defaults to CASCADE. Audit FKs (`updated_by`, `deleted_by`, `status_changed_by`, `manager_id`) use `nullOnDelete`.
- **Soft deletes** on `banks`, `branches`, `products`, `quotations`, `loan_details`, `customers`, `bank_charges`. Others are hard-delete.
- **Audit columns** via `HasAuditColumns` trait: auto-fills `updated_by` and `deleted_by` from `auth()->id()`.
- **Timestamps** on all tables except pure pivots and a couple of ledger tables (`stage_transfers`, `query_responses`, `app_settings` which have only `created_at` or nothing).
- **JSON columns** cast via model `$casts` — always pass raw arrays, never `json_encode` yourself. See `.claude/database-schema.md` for the full JSON cheat sheet.
- **Primary keys**: bigint autoincrement for all app tables; a couple of pivots use composite PKs (`role_user`, `role_permission`).

## Indexes

Every FK has an implicit index. Explicitly added indexes (performance-driven):

- `loan_details`: `status`, `current_stage`, `customer_type`, `loan_number`
- `stage_assignments`: `stage_key`, `assigned_to`, `status`, `parent_stage_key`, UNIQUE (`loan_id`, `stage_key`)
- `stage_queries`: (`stage_assignment_id`, `status`), `loan_id`
- `shf_notifications`: (`user_id`, `is_read`), `loan_id`
- `activity_logs`: `user_id`, (`subject_type`, `subject_id`), `created_at`
- `daily_visit_reports`: (`user_id`, `visit_date`), `follow_up_date`, (`follow_up_needed`, `is_follow_up_done`)
- `general_tasks`: (`created_by`, `status`), (`assigned_to`, `status`)
- `stages`: `stage_key`, `sequence_order`, `parent_stage_key`
- `loan_documents`: `loan_id`, (`loan_id`, `status`)
- UNIQUE composites on pivots: `user_branches` (user, branch), `location_user`, `location_product`, `bank_location`, `product_stages` (product, stage), `bank_employees` (bank, user, location), `bank_stage_configs` (bank, stage), `locations` (name, parent_id).

## Migration practice (Laravel 12)

- Use `php artisan make:migration --no-interaction` to scaffold.
- When **modifying** columns, include **all previous attributes** (type, nullable, default). Partial `->change()` calls drop prior flags.
- Keep migration files idempotent where possible — use `Schema::hasColumn()` guards if a migration might run on databases that already have the column.
- Register new tables and columns in the relevant model's `$fillable` + `casts()`.
- After schema changes, update `.claude/database-schema.md` and `.docs/models.md`.

## Seeder flow

`DatabaseSeeder::run()` calls `DefaultDataSeeder::run()`. `DefaultDataSeeder` seeds in order:

1. Permissions (from `config/permissions.php`)
2. Locations (Gujarat → Rajkot)
3. Branches (Rajkot Main Office)
4. Banks (HDFC, ICICI, Axis, Kotak)
5. Stages (17 enabled, including parallel sub-stages)
6. `bank_stage_configs` for known overrides (e.g., Axis rate_pf phase 0 = office_employee)
7. Roles (7) + role_user for the super admin
8. `role_permission` — per `seedRolePermissionMappings()`
9. user_branches, bank_location, bank_employees, location_user
10. `bank_charges` (initial values)
11. Products + `product_stages` (+ `product_stage_users` placeholders)
12. `app_config` initial row
13. `app_settings` key-values
14. Optional: sample Quotation + LoanDetail for development

`PermissionSeeder` is called from migrations to keep the catalog in sync when permissions change.

## Modifying the schema — checklist

1. Write/modify migration.
2. Update model: `$fillable`, `casts()`, relationships, scopes as needed.
3. Update `.claude/database-schema.md` — column row(s).
4. Update `.docs/models.md` if the model surface changed.
5. Update any seeder that relied on the old shape.
6. Update any service method that reads/writes the column (see `.claude/services-reference.md`).
7. `php artisan migrate:fresh --seed` locally; run `php artisan test --compact`.
8. Format changed PHP with `vendor/bin/pint --dirty --format agent`.

## See also

- `.claude/database-schema.md` — every column
- `models.md` — model surface area
- Laravel docs: `search-docs` MCP, topics `migrations`, `eloquent relationships`
