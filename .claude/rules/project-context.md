# Project Context

## Domain
Shreenathji Home Finance (SHF) -- a bilingual (English/Gujarati) loan management platform for the Indian financial services market. Generates comparison PDFs across multiple banks showing EMI calculations, processing charges, and required documents. Includes a full **loan task management system** with 12-stage workflow (including 5 parallel sub-stages with multi-phase role handoffs), document collection, stage assignments/transfers, two-way queries, notifications, disbursement tracking, and lifecycle timeline. Also includes a **general task management system** for personal/delegated tasks and a **daily visit report (DVR) system** for field activity tracking.

## Business Logic
- **Customer types**: proprietor, partnership_llp, pvt_ltd, salaried -- each has different document requirements (defined in `config/app-defaults.php`, both English and Gujarati)
- **Indian currency**: `₹ X,XX,XXX` format (Indian comma system: lakh/crore via `NumberToWordsService` + `LoanDetail::formatIndianNumber` + client-side `SHF.formatIndianNumber`)
- **Bilingual**: All user-facing content in English + Gujarati (documents, labels, PDF content, role labels, stage names)
- **EMI calculation**: Standard reducing balance formula across multiple banks and tenures (default tenures: 5, 10, 15, 20 years)
- **IOM charges**: Threshold-based calculation -- fixed charge below threshold, percentage above (configurable via `iomCharges` in app-defaults)
- **GST**: Configurable percentage (default 18%)
- **Activity logging**: `ActivityLog::log($action, $subject, $properties)` for all user-facing actions

## Brand
- **Colors**: Dark gray `#3a3536`, Accent orange `#f15a29`, Warm accent `#f47929`, Light accent `#f99d3e`, Light gray `#f8f8f8`
- **Fonts**: Jost (display/headings, 400-700 weights), Archivo (body text, 400-600 weights) -- local woff2 files in `public/fonts/`
- **CSS prefix**: `shf-` for all custom classes in `public/css/shf.css`

## Loan Task System
- **Stages**: Inquiry -> Document Selection -> Document Collection -> Parallel Processing (parent):
  - 4a. Application Number -> 4b. BSM/OSV -> 4c. Legal Verification (3-phase) + 4d. Technical Valuation + 4e. Property Valuation (parallel)
  -> Sanction Decision (approve/escalate/reject gate) -> Rate & PF (3-phase) -> Sanction Letter (3-phase) -> Docket Login (3-phase) -> KFS -> E-Sign & eNACH (4-phase) -> Disbursement -> OTC Clearance (cheque only)
  - With `OPEN_RATE_PF_PARALLEL=1`, Rate & PF runs in parallel with the 4c/4d/4e sub-stages from BSM/OSV onward, and Sanction Letter waits for both parallel_processing and Rate & PF.
- **Multi-phase stages**: Legal Verification (3-phase), Rate & PF (3-phase), Sanction Letter (3-phase), Docket Login (3-phase), E-Sign & eNACH (4-phase) -- role handoffs between loan_advisor, bank_employee, and office_employee
- **Stage model**: `Stage` with `stage_key`, `stage_name_en`/`stage_name_gu`, `sequence_order`, `is_parallel`, `parent_stage_key`, `stage_type` (including 'decision'), `default_role` (JSON array), `sub_actions` (JSON array). Scopes: `enabled()`, `mainStages()`, `subStagesOf()`
- **33 Eloquent models** across core, quotation, loan, workflow, task, DVR, and config domains
- **14 services** + **24 controllers** (21 web + 3 API)
- **Loan statuses**: active, completed, rejected, cancelled, on_hold (with color labels)
- Auto-assignment by role/bank/branch/product (priority-based), manual transfer with history via `StageTransfer` model
- Two-way query system (`StageQuery` + `QueryResponse`) blocks stage completion until resolved
- In-app notifications (`ShfNotification`) with bell badge and 60-second polling
- Impersonation: `lab404/laravel-impersonate` (super_admin or env-flagged admins via `app.allow_impersonate_all`)

## General Task System
- Personal/delegated tasks -- separate from loan workflow
- Any user creates tasks for themselves or assigns to others
- Statuses: pending, in_progress, completed, cancelled
- Priorities: low, normal, high, urgent (each with badge class)
- Optional loan link via `loan_detail_id` (search by loan #, app #, customer name)
- Comments via `GeneralTaskComment`, status changes, notifications on assignment/completion/comments
- BDH sees branch users' tasks via `user_branches` pivot
- Admin/super_admin: `view_all_tasks` permission (read-only)
- Dashboard integration: "Personal Tasks" tab with create modal

## DVR System
- **Daily Visit Reports**: Field activity tracking for loan advisors and branch staff
- Fields: visit_date, contact_name, contact_phone, contact_type, purpose, notes, outcome
- **Contact types** (configurable): existing_customer, new_customer, CA, builder/developer, DSA/connector, other
- **Visit purposes** (configurable): new_lead, follow_up, document_collection, quotation_delivery, payment/disbursement, relationship, other
- **Follow-up tracking**: `follow_up_needed`, `follow_up_date`, `follow_up_notes`, `is_follow_up_done`
- **Visit chain**: Linked visits via `parent_visit_id` / `follow_up_visit_id` with `getVisitChain()` method
- **Optional linking**: Can link to quotation (`quotation_id`) or loan (`loan_id`)
- **Branch association**: Each visit tied to a branch
- **Visibility** (via `scopeVisibleTo`):
  - `view_all_dvr` permission: see everything
  - BDH/branch_manager: own visits + branch users' visits
  - Others: own visits only
- **Permissions**: view_dvr, create_dvr, edit_dvr, delete_dvr, view_all_dvr

## Unified Role System (7 Roles)
- **super_admin**: Full system access, bypasses all permissions
- **admin**: System administration, settings, user management
- **branch_manager**: Branch-level management, quotations, loan stages (`can_be_advisor = true`)
- **bdh**: Business Development Head, same access as Branch Manager (`can_be_advisor = true`)
- **loan_advisor**: Quotation creation, loan processing stages (`can_be_advisor = true`)
- **bank_employee**: Bank-side loan processing only
- **office_employee**: Office operations, valuations, docket review, OTC
- Managed via `roles` table + `role_user` pivot (users can have multiple roles)
- `Role::advisorEligibleSlugs()` returns cached list of roles with `can_be_advisor = true` (5-minute cache)
- Gujarati labels in `Role::gujaratiLabels()`: super_admin -> 'સુપર એડમિન', admin -> 'એડમિન', branch_manager -> 'બ્રાન્ચ મેનેજર', bdh -> 'બિઝનેસ ડેવલપમેન્ટ હેડ', loan_advisor -> 'લોન સલાહકાર', bank_employee -> 'બેંક કર્મચારી', office_employee -> 'ઓફિસ કર્મચારી'

## Permission System
- **44 permissions** in **7 groups**: Settings (8), Quotations (8), Users (5), Loans (14), Tasks (1), DVR (5), System (3)
- Defined in `config/permissions.php`, seeded via migration
- 3-tier resolution: super_admin bypass -> user_permissions grant/deny -> role_permission (any role grants)
- Middleware: `permission:slug_name` on routes, `active` on all web routes
- Implementation: `PermissionService::userHasPermission()` called via `User::hasPermission()`
- Cache: 5-minute TTL per user/role

## Config System
- `ConfigService` reads from `app_config` table (key-value: `config_key` + `config_json` JSON cast) with `config/app-defaults.php` fallback
- Methods: `load()`, `save()`, `reset()`, `get(dotKey)`, `updateSection()`, `updateMany()`
- Merges DB config with defaults via `array_replace_recursive`, with sequential arrays replaced entirely from DB (so deletions from defaults are respected)
- `AppConfig` model uses key-value pattern with 'main' as the primary config key
- Default config includes: company info, banks, IOM charges, tenures, documents (EN/GU by customer type), DVR contact types, DVR purposes, GST percent, services

## Loan Visibility
- `LoanDetail::scopeVisibleTo($query, $user)` controls who sees what:
  - `view_all_loans` permission: see everything
  - Own loans: `created_by` = user OR `assigned_advisor` = user
  - Stage assigned: user has any `StageAssignment` on the loan
  - Branch-based: branch_manager/bdh see loans in their branches (via `user_branches` pivot)
  - Transfer history: bank_employee/office_employee see loans they appear in `stage_transfers` (transferred_from or transferred_to)

## Task Visibility
- `GeneralTask::scopeVisibleTo($query, $user)` controls who sees what:
  - `view_all_tasks` permission: see everything (admin, read-only)
  - Own tasks: `created_by` = user OR `assigned_to` = user
  - BDH: also sees tasks from users in their branches (via `user_branches` pivot, both created_by and assigned_to)
