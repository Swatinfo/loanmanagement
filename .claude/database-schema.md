# Database Schema Reference

Complete schema for SHF Loan Quotation (Laravel 12 + SQLite). All tables, columns, foreign keys, indexes, and seed behavior derived from migrations and models.

**Total: 40 application tables + 9 Laravel framework tables**

## Conventions

- All FK `onDelete` defaults to CASCADE unless noted.
- `timestamps` = standard `created_at` + `updated_at`.
- `soft_deletes` = `deleted_at` nullable column.
- `audit_columns` = `updated_by` + `deleted_by` FK to `users.id` nullOnDelete.

---

## Core Tables

### users

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint PK | no | AI | |
| name | string | no | | |
| email | string | no | | UNIQUE |
| email_verified_at | timestamp | yes | | |
| password | string | no | | hashed |
| is_active | boolean | no | true | |
| created_by | bigint FK users.id | yes | | nullOnDelete |
| phone | varchar(20) | yes | | |
| employee_id | string | yes | | |
| default_branch_id | bigint FK branches.id | yes | | nullOnDelete |
| task_bank_id | bigint FK banks.id | yes | | nullOnDelete |
| remember_token | string | yes | | |
| timestamps | | | | |

### roles

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| name | string | |
| slug | string | UNIQUE |
| description | string | nullable |
| can_be_advisor | boolean | default false |
| is_system | boolean | default false (super_admin, admin are system) |
| timestamps | | |

### role_user (pivot)
`user_id`, `role_id` — composite PK; both CASCADE.

### permissions

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| name | string | human name |
| slug | string | UNIQUE, used in `permission:slug` middleware |
| group | string | Settings, Quotations, Users, Loans, Tasks, DVR, System |
| description | string | nullable |
| timestamps | | |

### role_permission (pivot)
`role_id`, `permission_id` — composite PK; both CASCADE.

### user_permissions

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| user_id | FK users.id | CASCADE |
| permission_id | FK permissions.id | CASCADE |
| type | enum('grant','deny') | |
| timestamps | | |

### user_branches (pivot)

| Column | Type | Notes |
|---|---|---|
| user_id | FK users.id | CASCADE |
| branch_id | FK branches.id | CASCADE |
| is_default_office_employee | boolean | default false |
| UNIQUE (user_id, branch_id) | | |
| timestamps | | |

---

## Geography & Organization

### locations

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| parent_id | FK locations.id | nullable (cities have parent = state) |
| name | string | |
| type | enum('state','city') | |
| code | varchar(20) | nullable |
| is_active | boolean | default true |
| UNIQUE (name, parent_id) | | |
| timestamps | | |

### branches

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| name | string | |
| code | string | UNIQUE, nullable |
| address | text | nullable |
| city | string | nullable |
| phone | varchar(20) | nullable |
| is_active | boolean | default true |
| manager_id | FK users.id | nullable |
| location_id | FK locations.id | nullable |
| audit_columns, soft_deletes, timestamps | | |

### location_user (pivot)
`location_id`, `user_id` — UNIQUE; both CASCADE.

### location_product (pivot)
`location_id`, `product_id` — UNIQUE; both CASCADE.

### bank_location (pivot)
`bank_id`, `location_id` — UNIQUE; both CASCADE.

---

## Banks & Products

### banks

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| name | string | UNIQUE |
| code | string | nullable |
| is_active | boolean | default true |
| audit_columns, soft_deletes, timestamps | | |

### bank_charges

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| bank_name | string | |
| pf | decimal(5,2) | default 0 (processing fee %) |
| admin | unsignedBigInt | default 0 |
| stamp_notary | unsignedBigInt | default 0 |
| registration_fee | unsignedBigInt | default 0 |
| advocate | unsignedBigInt | default 0 |
| tc | unsignedBigInt | default 0 |
| extra1_name, extra1_amt | string / int | nullable |
| extra2_name, extra2_amt | string / int | nullable |
| timestamps | | |

### bank_employees (pivot)

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| bank_id | FK banks.id | CASCADE |
| user_id | FK users.id | CASCADE |
| location_id | FK locations.id | nullable |
| is_default | boolean | default false |
| UNIQUE (bank_id, user_id, location_id) | | |
| timestamps | | |

### products

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| bank_id | FK banks.id | CASCADE |
| name | string | |
| code | string | nullable |
| is_active | boolean | default true |
| UNIQUE (bank_id, name) | | |
| audit_columns, soft_deletes, timestamps | | |

### bank_stage_configs

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| bank_id | FK banks.id | CASCADE |
| stage_id | FK stages.id | CASCADE |
| assigned_role | varchar(50) | nullable (overrides Stage.assigned_role) |
| phase_roles | json | nullable (overrides Stage.sub_actions[].role per phase) |
| UNIQUE (bank_id, stage_id) | | |
| timestamps | | |

---

## Workflow Stages

### stages

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| stage_key | string | UNIQUE — e.g., `inquiry`, `document_collection`, `parallel_processing` |
| is_enabled | boolean | default true |
| stage_name_en | string | |
| stage_name_gu | string | nullable |
| sequence_order | int | INDEX |
| is_parallel | boolean | default false |
| parent_stage_key | string | nullable, INDEX (for sub-stages of `parallel_processing`) |
| stage_type | string | default 'sequential' — sequential / parallel / decision |
| description_en, description_gu | text | nullable |
| default_role | string | nullable (stores JSON text — array of eligible role slugs, cast to array in model) |
| assigned_role | varchar(50) | default 'task_owner' |
| sub_actions | json | phase definitions: `[{name, role, ...}]` |
| timestamps | | |

**Main stages (sequence_order):** 1 inquiry → 2 document_selection → 3 document_collection → 4 parallel_processing → 5 rate_pf → 6 sanction → 7 docket → 8 kfs → 9 esign → 10 disbursement → 11 otc_clearance.

**Parallel sub-stages** (parent_stage_key = `parallel_processing`): app_number (4a), bsm_osv (4b), legal_verification (4c), technical_valuation (4d), sanction_decision (4e).

### product_stages

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| product_id | FK products.id | CASCADE |
| stage_id | FK stages.id | CASCADE |
| is_enabled | boolean | default true |
| default_assignee_role | string | nullable |
| default_user_id | FK users.id | nullable |
| auto_skip | boolean | default false |
| allow_skip | boolean | default false |
| sort_order | int | nullable |
| sub_actions_override | json | nullable — per-phase role/user overrides |
| UNIQUE (product_id, stage_id) | | |
| audit_columns, timestamps | | |

### product_stage_users

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| product_stage_id | FK product_stages.id | CASCADE |
| branch_id | FK branches.id | nullable |
| location_id | FK locations.id | nullable |
| user_id | FK users.id | CASCADE |
| is_default | boolean | default false |
| phase_index | int | nullable (which phase this assignment covers) |
| timestamps | | |

---

## Customers & Quotations

### customers

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| customer_name | string | |
| mobile | varchar(20) | nullable |
| email | string | nullable |
| date_of_birth | date | nullable |
| pan_number | varchar(10) | nullable |
| audit_columns, soft_deletes, timestamps | | |

### quotations

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| user_id | FK users.id | CASCADE |
| loan_id | FK loan_details.id | nullable (set when converted) |
| customer_name | string | |
| customer_type | string | proprietor / partnership_llp / pvt_ltd / salaried |
| loan_amount | unsignedBigInt | |
| pdf_filename, pdf_path | string | nullable |
| additional_notes | text | nullable |
| prepared_by_name, prepared_by_mobile | string | |
| selected_tenures | json | |
| location_id | FK locations.id | nullable |
| branch_id | FK branches.id | nullable |
| status | string(20) | default `active`, INDEX — `active` / `on_hold` / `cancelled` |
| hold_reason_key, cancel_reason_key | varchar(50) | nullable; keys into `quotationHoldReasons` / `quotationCancelReasons` config |
| hold_note, cancel_note | text | nullable |
| hold_follow_up_date | date | nullable, INDEX |
| held_at, cancelled_at | timestamp | nullable |
| held_by, cancelled_by | FK users.id | nullable, nullOnDelete |
| audit_columns, soft_deletes, timestamps | | |

### quotation_banks

Per-bank rate/charge row per quotation. FK `quotation_id` CASCADE. Fields: bank_name, roi_min/max decimal(5,2), pf_charge decimal(5,2), admin_charge, stamp_notary, registration_fee, advocate_fees, iom_charge, tc_report, extra1/2 name+amount, total_charges (all unsignedBigInt).

### quotation_emi
| Column | Type | Notes |
|---|---|---|
| id | PK | |
| quotation_bank_id | FK quotation_banks.id | CASCADE |
| tenure_years | int | |
| monthly_emi, total_interest, total_payment | unsignedBigInt | |
| timestamps | | |

**Table name:** `quotation_emi` (singular).

### quotation_documents
`id`, `quotation_id` (FK CASCADE), `document_name_en`, `document_name_gu`, timestamps.

---

## Loans

### loan_details

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| loan_number | string | UNIQUE, format `SHF-YYYYMM-NNNN` |
| quotation_id | FK quotations.id | nullable |
| customer_id | FK customers.id | nullable |
| branch_id | FK branches.id | nullable |
| bank_id | FK banks.id | nullable |
| product_id | FK products.id | nullable |
| location_id | FK locations.id | nullable |
| customer_name | string | |
| customer_type | string | INDEX |
| customer_phone | varchar(20) | nullable |
| customer_email | string | nullable |
| date_of_birth | date | nullable |
| pan_number | varchar(10) | nullable, uppercase |
| loan_amount | unsignedBigInt | |
| status | string | INDEX — active / on_hold / completed / rejected / cancelled |
| is_sanctioned | boolean | default false |
| current_stage | string | INDEX — default `inquiry` |
| bank_name | string | denormalized from bank |
| roi_min, roi_max | decimal(5,2) | |
| total_charges | string | nullable |
| application_number | string | nullable — set during app_number stage |
| assigned_bank_employee | FK users.id | nullable |
| assigned_advisor | FK users.id | nullable |
| created_by | FK users.id | cascadeOnDelete |
| due_date | date | nullable |
| expected_docket_date | date | nullable, auto-calculated from sanction |
| rejected_at | timestamp | nullable |
| rejected_by | FK users.id | nullable |
| rejected_stage | string | nullable |
| rejection_reason | text | nullable |
| status_reason | text | nullable |
| status_changed_at | timestamp | nullable |
| status_changed_by | FK users.id | nullable |
| workflow_config | json | nullable — frozen snapshot at loan creation |
| notes | text | nullable |
| audit_columns, soft_deletes, timestamps | | |

### loan_documents

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| loan_id | FK loan_details.id | CASCADE, INDEX |
| document_name_en, document_name_gu | string | |
| is_required | boolean | default true |
| status | string | INDEX — pending / received / rejected / waived |
| received_date | date | nullable |
| received_by | FK users.id | nullable |
| rejected_reason | text | nullable |
| notes | text | nullable |
| sort_order | int | default 0 |
| file_path, file_name, file_mime | string | nullable |
| file_size | unsignedBigInt | nullable |
| uploaded_by | FK users.id | nullable |
| uploaded_at | timestamp | nullable |
| audit_columns, timestamps | | |

### loan_progress

One row per loan (`loan_id` UNIQUE). Fields: `total_stages` (int, default 10), `completed_stages` (int), `overall_percentage` (decimal 5,2), `estimated_completion` (date nullable), `workflow_snapshot` (text nullable — stores JSON text of current stage statuses, cast to array in model), timestamps.

### stage_assignments

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| loan_id | FK loan_details.id | CASCADE |
| stage_key | string | INDEX |
| assigned_to | FK users.id | nullable, INDEX |
| status | string | INDEX — pending / in_progress / completed / rejected / skipped |
| previous_status | string | nullable |
| priority | string | default normal |
| started_at, completed_at | timestamp | nullable |
| completed_by | FK users.id | nullable |
| is_parallel_stage | boolean | default false |
| parent_stage_key | string | nullable, INDEX |
| notes | text | nullable — stores JSON text of phase/form data per stage (cast to array in model) |
| UNIQUE (loan_id, stage_key) | | |
| audit_columns, timestamps | | |

### stage_transfers

Ledger row per transfer. `stage_assignment_id` (CASCADE), `loan_id` (CASCADE), `stage_key`, `transferred_from` + `transferred_to` (FK users.id CASCADE), `reason` (text nullable), `transfer_type` (default 'manual'), `created_at` only (no updated_at).

### stage_queries

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| stage_assignment_id | FK stage_assignments.id | CASCADE |
| loan_id | FK loan_details.id | CASCADE, INDEX |
| stage_key | string | |
| query_text | text | |
| raised_by | FK users.id | CASCADE |
| status | string | default pending — pending / responded / resolved |
| resolved_at | timestamp | nullable |
| resolved_by | FK users.id | nullable |
| INDEX (stage_assignment_id, status) | | |
| timestamps | | |

### query_responses

`stage_query_id` (CASCADE), `response_text`, `responded_by` (FK users.id CASCADE), `created_at` only.

### remarks

`loan_id` (CASCADE INDEX), `stage_key` (nullable INDEX — null = general remark), `user_id` (CASCADE), `remark` (text), timestamps.

### valuation_details

One or more rows per loan. Fields: `loan_id` (CASCADE INDEX), `valuation_type` (default 'property'), `property_address` (text nullable), `latitude/longitude` (varchar 50), `landmark`, `property_type`, `land_area` (string), `land_rate` (decimal 12,2), `land_valuation` (unsignedBigInt nullable), `construction_area` (string), `construction_rate` (decimal 12,2), `construction_valuation` (unsignedBigInt nullable), `final_valuation` (unsignedBigInt nullable), `market_value` (unsignedBigInt nullable), `government_value` (unsignedBigInt nullable), `valuation_date`, `valuator_name`, `valuator_report_number`, `notes`, audit_columns, timestamps.

### disbursement_details

One row per loan (`loan_id` UNIQUE CASCADE). Fields: `disbursement_type` (fund_transfer / cheque / demand_draft), `disbursement_date` (date nullable), `amount_disbursed` (unsignedBigInt nullable), `bank_account_number`, `ifsc_code`, `cheque_number`, `cheque_date`, `cheques` (json — array for multiple cheques), `dd_number`, `dd_date`, `is_otc` (boolean), `otc_branch`, `otc_cleared` (boolean), `otc_cleared_date`, `otc_cleared_by` (FK users.id nullable), `reference_number`, `notes`, audit_columns, timestamps.

---

## Notifications, Activity, Config

### shf_notifications

`user_id` (CASCADE), `title`, `message`, `type` (default 'info' — info/success/warning/error/stage_update/assignment), `is_read` (boolean default false), `loan_id` (FK nullable INDEX), `stage_key`, `link`, INDEX (user_id, is_read), timestamps.

### activity_logs

`user_id` (nullOnDelete INDEX), `action` (string), `subject_type` + `subject_id` (polymorphic INDEX), `properties` (json), `ip_address` (varchar 45), `user_agent` (text), INDEX (created_at), timestamps.

### activity_log (Spatie)

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| log_name | string | nullable, INDEX |
| description | text | |
| subject_type | string | nullable (nullableMorphs, paired with subject_id) |
| subject_id | unsignedBigInt | nullable, INDEX |
| event | string | nullable |
| causer_type | string | nullable (nullableMorphs, paired with causer_id) |
| causer_id | unsignedBigInt | nullable, INDEX |
| attribute_changes | json | nullable (Spatie maps this as `properties`) |
| properties | json | nullable |
| batch_uuid | uuid | nullable |
| ip_address | varchar(45) | nullable — custom SHF field |
| user_agent | text | nullable — custom SHF field |
| timestamps | | |

Created by `2026_04_17_125136_create_activity_log_table.php`. Coexists with legacy `activity_logs` table.

### push_subscriptions

| Column | Type | Notes |
|---|---|---|
| id | PK (bigIncrements) | |
| subscribable_type | string | morphs (paired with subscribable_id), INDEX |
| subscribable_id | unsignedBigInt | INDEX |
| endpoint | varchar(500) | UNIQUE |
| public_key | string | nullable |
| auth_token | string | nullable |
| content_encoding | string | nullable |
| timestamps | | |

Created by `2026_04_17_143109_create_push_subscriptions_table.php` (laravel-notification-channels/webpush). Uses `webpush.table_name` config.

### app_config

Key-value store. `config_key` UNIQUE (primary key = `'main'`), `config_json` (longText, cast to array), timestamps.

### app_settings

Simple key-value. `setting_key` (PK string), `setting_value` (text), `updated_at` only.

### task_role_permissions (legacy)

`task_role` (varchar), `permission_id` (CASCADE), UNIQUE pair. Legacy — superseded by `role_permission` after unified roles migration.

---

## General Tasks & DVR

### general_tasks

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| title | string | |
| description | text | nullable |
| created_by | FK users.id | |
| assigned_to | FK users.id | nullable |
| loan_detail_id | FK loan_details.id | nullable |
| quotation_id | FK quotations.id | nullable, nullOnDelete |
| status | string | default pending — pending / in_progress / completed / cancelled |
| priority | string | default normal — low / normal / high / urgent |
| due_date | date | nullable |
| completed_at | timestamp | nullable |
| INDEX (created_by, status) | | |
| INDEX (assigned_to, status) | | |
| timestamps | | |

### general_task_comments

`general_task_id` (CASCADE), `user_id` (CASCADE), `body` (text), timestamps.

### daily_visit_reports

| Column | Type | Notes |
|---|---|---|
| id | PK | |
| user_id | FK users.id | CASCADE |
| visit_date | date | |
| contact_name | string | |
| contact_phone | varchar(20) | nullable |
| contact_type | string | — configurable (see `dvrContactTypes` in app-defaults) |
| purpose | string | — configurable (see `dvrPurposes` in app-defaults) |
| notes, outcome, follow_up_notes | text | nullable |
| follow_up_needed | boolean | default false |
| follow_up_date | date | nullable, INDEX |
| is_follow_up_done | boolean | default false |
| parent_visit_id | FK daily_visit_reports.id | nullable (for follow-up chain) |
| follow_up_visit_id | FK daily_visit_reports.id | nullable |
| quotation_id | FK quotations.id | nullable |
| loan_id | FK loan_details.id | nullable |
| branch_id | FK branches.id | nullable |
| INDEX (user_id, visit_date) | | |
| INDEX (follow_up_needed, is_follow_up_done) | | |
| timestamps | | |

---

## Framework Tables (Laravel defaults)

`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `sessions`.

---

## Permissions Catalog

**44 permissions across 7 groups.** Managed via `config/permissions.php`, seeded into `permissions` table, assigned via `role_permission` pivot. Note: `skip_loan_stages` was removed in `2026_04_09_211216_create_unified_roles_system`.

| Group | Slugs |
|---|---|
| Settings (8) | view_settings, edit_company_info, edit_banks, edit_documents, edit_tenures, edit_charges, edit_services, edit_gst |
| Quotations (11) | create_quotation, generate_pdf, view_own_quotations, view_all_quotations, delete_quotations, download_pdf, download_pdf_branded, download_pdf_plain, hold_quotation, cancel_quotation, resume_quotation |
| Users (5) | view_users, create_users, edit_users, delete_users, assign_roles |
| Loans (13) | convert_to_loan, view_loans, view_all_loans, create_loan, edit_loan, delete_loan, manage_loan_documents, upload_loan_documents, download_loan_documents, delete_loan_files, manage_loan_stages, add_remarks, manage_workflow_config |
| Tasks (1) | view_all_tasks |
| DVR (5) | view_dvr, create_dvr, edit_dvr, delete_dvr, view_all_dvr |
| System (4) | change_own_password, manage_permissions, view_activity_log, view_reports |

**Additional extensions** (seeded in migrations beyond config): `manage_customers`, `view_customers`, `impersonate_users`, `view_dashboard`, `manage_notifications`, `transfer_loan_stages`, `reject_loan`, `change_loan_status`, `view_loan_timeline`, `manage_disbursement`, `manage_valuation`, `raise_query`, `resolve_query`.

### Role → default permission map

- **super_admin** — all permissions (bypass via `PermissionService`)
- **admin** — all except `delete_users`, `delete_loan`, branded/plain PDF variants
- **branch_manager / bdh** — quotations, loans (not delete), remarks, advisor + manager tasks
- **loan_advisor** — create_quotation, generate_pdf, view_own_quotations, download_pdf, change_own_password, view_dashboard, manage_notifications, convert_to_loan, view_loans, create_loan, edit_loan, manage_loan_documents, manage_loan_stages, add_remarks, upload_loan_documents, download_loan_documents, manage_customers, view_customers, transfer_loan_stages, reject_loan, change_loan_status, view_loan_timeline, manage_disbursement, raise_query, resolve_query
- **bank_employee** — change_own_password, view_dashboard, manage_notifications, view_loans, add_remarks, download_loan_documents, view_customers, view_loan_timeline, raise_query
- **office_employee** — change_own_password, view_dashboard, manage_notifications, view_loans, edit_loan, manage_loan_documents, manage_loan_stages, add_remarks, upload_loan_documents, download_loan_documents, view_customers, transfer_loan_stages, reject_loan, change_loan_status, view_loan_timeline, manage_valuation, raise_query

### Resolution order (PermissionService)

1. `super_admin` role → always `true` (bypass)
2. `user_permissions` row for (user, permission) → `grant` or `deny`
3. Any role in `role_user` → any `role_permission` row grants → `true`
4. Otherwise → `false`

**Cache:** 300s TTL on three keys — `user_perms:{userId}`, `user_role_ids:{userId}`, `role_perms:{sortedRoleIds}`. Invalidated via `PermissionService::clearUserCache()`, `clearRoleCache()`, `clearAllCaches()`.

---

## Seed data (DefaultDataSeeder)

- **Locations**: Gujarat (state) + Rajkot (city)
- **Branch**: Rajkot Main Office
- **Banks**: HDFC Bank, ICICI Bank, Axis Bank, Kotak Mahindra Bank
- **Stages**: 17 enabled stages including 5 parallel sub-stages
- **Roles**: 7 (super_admin, admin, branch_manager, bdh, loan_advisor, bank_employee, office_employee)
- **Products**: multiple per bank
- **Users**: 1 admin + sample per role (development seeds only)

---

## JSON columns cheat sheet

Columns below are either native `json` columns or `text`/`string` columns that hold JSON text decoded via model casts. The model-level cast is noted where the underlying column is not a native JSON type.

| Table.column | Storage | Shape |
|---|---|---|
| stages.default_role | string (cast to array in model) | `["branch_manager", "loan_advisor", ...]` |
| stages.sub_actions | json | `[{ "name": "send_for_sanction", "role": "bank_employee" }, ...]` |
| product_stages.sub_actions_override | json | same shape as stages.sub_actions per-product override |
| bank_stage_configs.phase_roles | json | `{ "0": "office_employee", "1": "bank_employee" }` |
| stage_assignments.notes | text (cast to array in model) | free-form per-stage form values (phase fields, decisions) |
| loan_details.workflow_config | json | snapshot `{ stage_key: { role, default_user_id, phases: {idx: {role, default_user_id}} } }` |
| loan_progress.workflow_snapshot | text (cast to array in model) | `{ stage_key: { status, assigned_to } }` |
| disbursement_details.cheques | json | `[{ cheque_name, cheque_number, cheque_date, cheque_amount }, ...]` |
| quotations.selected_tenures | json | `[5, 10, 15, 20]` |
| app_config.config_json | longText | full defaults tree (see `config/app-defaults.php`) |
| activity_logs.properties | json | arbitrary event snapshot |
| activity_log.properties / attribute_changes | json | Spatie event payload |
