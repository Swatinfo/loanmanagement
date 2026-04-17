# Loans

Full loan lifecycle management. CRUD, status changes, visibility scoping, valuations, disbursement, remarks. For the workflow engine itself (stages, phases, transfers) see `workflow-developer.md`.

## Routes & permissions

Complete list: `.claude/routes-reference.md`. Summary:

| Area | Route prefix | Permissions |
|---|---|---|
| CRUD | `/loans` | `view_loans`, `create_loan`, `edit_loan`, `delete_loan` |
| Status | `/loans/{id}/status` | `edit_loan` |
| Stages | `/loans/{id}/stages/...` | `manage_loan_stages`, `transfer_loan_stages`, `skip_loan_stages` |
| Documents | `/loans/{id}/documents` | `manage_loan_documents`, `upload_loan_documents`, `download_loan_documents`, `delete_loan_files` |
| Valuation | `/loans/{id}/valuation` | `manage_loan_stages` |
| Disbursement | `/loans/{id}/disbursement` | `manage_loan_stages` |
| Remarks | `/loans/{id}/remarks` | `view_loans`, `add_remarks` |
| Timeline | `/loans/{id}/timeline` | `view_loans` |
| Transfers history | `/loans/{id}/transfers` | `view_loans` |

## Model

`LoanDetail` — see `.claude/database-schema.md` and `models.md`. Key scopes and accessors:

- `scopeVisibleTo(User)` — visibility rules (see below)
- `scopeActive()` — `status = 'active'`
- `formattedAmount` — `₹ X,XX,XXX`
- `statusLabel`, `statusColor`, `customerTypeLabel`
- `currentStageName`, `currentOwner`, `timeWithCurrentOwner`, `totalLoanTime`
- `stageBadgeHtml` — HTML badge(s) for the current stage; expands parallel sub-stages with role suffix
- `isBasicEditLocked` — true once the `app_number` stage is completed (basic details become read-only)

Static:
- `LoanDetail::generateLoanNumber()` → `SHF-YYYYMM-NNNN` (zero-padded incrementing)
- `LoanDetail::userRoleSlug($user)` → primary workflow role slug (priority: bank_employee → office_employee → bdh → branch_manager → loan_advisor)
- `LoanDetail::roleSuffix($slug)` → "Bank Review", "Office", "BDH", "SHF"
- `LoanDetail::stageBadgeClass($stageKey)` → CSS class for stage badge

## Visibility scope

`LoanDetail::scopeVisibleTo($query, $user)`:

1. If user has `view_all_loans` permission → sees everything
2. Else OR-union:
   - Own loans (`created_by = user` OR `assigned_advisor = user`)
   - Loans where the user has any `StageAssignment` (currently assigned to a stage)
   - If `branch_manager` or `bdh`: loans in their branches (via `user_branches` pivot)
   - If `bank_employee` or `office_employee`: loans where they appear in `stage_transfers` history (transferred_from or transferred_to)

The DataTable endpoint (`/loans/data`) applies this scope before filtering/pagination.

## Loan status lifecycle

Statuses (from `LoanDetail::STATUS_*` constants):

- `active` — default; workflow stages actionable
- `on_hold` — paused; stages read-only, basic info read-only
- `cancelled` — terminal (soft); can be reactivated by super_admin / admin / branch_manager / bdh
- `rejected` — terminal (from `sanction_decision` or explicit stage rejection); includes `rejected_stage` + `rejection_reason`
- `completed` — terminal success; set by stage flow (OTC clearance complete, or fund-transfer disbursement which skips OTC)

### Status transitions (`LoanController::updateStatus`)

- `active` → `on_hold` (any editor)
- `active` → `cancelled` (super_admin/admin/branch_manager/bdh only)
- `on_hold` → `active`
- `cancelled` → `active` (reactivate, same elevated permission set)
- `rejected` → `active` (reactivate — clears `rejected_*` fields, restores rejected stages to in_progress, recalculates progress)

Every status change sets `status_reason`, `status_changed_at`, `status_changed_by` and is logged to `activity_logs`.

## Create flow

### Via quotation conversion

- Permission: `convert_to_loan`
- UI: `/quotations/{id}/convert`
- Controller: `LoanConversionController@convert`
- Service: `LoanConversionService::convertFromQuotation($q, $bankIndex, $extra)`
- Result: new loan starting at `current_stage = document_collection` (inquiry + document_selection auto-completed)

### Directly (no quotation)

- Permission: `create_loan` + `canCreateLoans()` (super_admin, admin, or any advisor-eligible role)
- UI: `/loans/create`
- Controller: `LoanController@store`
- Service: `LoanConversionService::createDirectLoan($data)`
- Result: new loan starting at `current_stage = inquiry`

### Common steps (both paths)

`LoanConversionService` runs inside a DB transaction:

1. Create/reuse `Customer` record
2. Create `LoanDetail` (fillable from form + some defaults)
3. `LoanDetail::generateLoanNumber()`
4. `LoanStageService::buildWorkflowSnapshot()` frozen into `loan_details.workflow_config`
5. Populate documents (from quotation or config defaults)
6. `LoanStageService::initializeStages()` — creates all `stage_assignments` + `loan_progress`
7. Auto-complete initial stages (quotation conversion only)
8. Auto-assign the first active stage
9. Log `ActivityLog` (loan_created / convert_to_loan)

## Edit flow

`LoanController@update`:
- Permission `edit_loan` + visibility check
- Blocks if `isBasicEditLocked()` (app_number stage completed)
- Same validation as create
- Updates bank_name from bank_id
- Tracks changed fields in `ActivityLog`

## Delete

`LoanController@destroy`:
- Permission: `delete_loan`
- Clears `loan_id` on any linked quotations (they become convertible again)
- Soft-deletes the loan
- Response: `{ success, redirect }`

## Show page

`/loans/{id}` — main loan detail.

Rendered sections (conditional on state):

1. **Ownership & time banner** — current advisor + total loan time
2. **Customer & loan info** (collapsible, closed)
3. **Current stage card** — linked to `/loans/{id}/stages`; progress bar; active query warning if any
4. **Parallel processing sub-stages** — when `current_stage = parallel_processing`, inline list with role suffixes
5. **Documents summary** — progress bar, inline list; on the `document_collection` stage it's prominent + links to `/loans/{id}/documents`
6. **Source quotation** (if converted)
7. **Notes** (if any)
8. **Remarks** — collapsible, AJAX-loaded via `GET /loans/{id}/remarks`, add via `POST`
9. **Status alert boxes** — rejected/on_hold/cancelled/completed banners

Status dropdown: Put on hold / Cancel / Reactivate (permissions-gated).

## Timeline

`/loans/{id}/timeline` — rendered via `LoanTimelineService::getTimeline($loan)` merging:

- Quotation created + converted (if from quotation)
- Loan created (direct)
- Each stage started / completed / skipped / rejected
- Transfers (from → to with reason)
- Queries raised + responses
- Remarks
- Loan rejected
- Disbursement processed
- Loan completed

Each entry: `{type, date, title, description, user, icon, color}`. Ordered by date.

## Documents

See also `workflow-developer.md` (document collection stage) and `.claude/services-reference.md` (`LoanDocumentService`).

### Model

`LoanDocument` — one row per required document. Statuses: `pending` / `received` / `rejected` / `waived`. File-storage fields: `file_path`, `file_name`, `file_size`, `file_mime`, `uploaded_by`, `uploaded_at`.

### Stage interaction

On the `document_collection` stage:
- Stage auto-advances when `LoanDocumentService::allRequiredResolved()` returns true
- Stage soft-reverts if the stage was completed but someone edits docs backward (e.g., marks one received → pending again)

### File storage

`storage/app/loan-documents/{loanId}/{docId}_{timestamp}.{ext}`. Mime + size checked on upload. Max 10 MB. Allowed: pdf, jpg, jpeg, png, webp, doc, docx, xls, xlsx.

## Valuation

`LoanValuationController`.

- `GET /loans/{id}/valuation` — form
- `GET /loans/{id}/valuation-map` — map view (Leaflet.js)
- `POST /loans/{id}/valuation` — upsert
- `GET /api/search-location?q=` — OSM Nominatim forward geocode
- `GET /api/reverse-geocode?lat=&lng=` — OSM reverse

Computes: `land_valuation = land_area × land_rate`, same for construction, `final_valuation = sum`, `market_value = final_valuation`.

**Auto-completes** the `technical_valuation` stage once a valuation exists (pending/in_progress → completed).

**Locked** when loan status ≠ active/on_hold.

## Disbursement

`LoanDisbursementController` + `DisbursementService::processDisbursement()`.

- `GET /loans/{id}/disbursement` — form
- `POST /loans/{id}/disbursement` — upsert + complete stage

### Types

- `fund_transfer` — direct NEFT/RTGS; **skips OTC stage**, marks loan `completed`
- `cheque` — issued cheque(s); opens `otc_clearance` stage for cheque tracking

### Validation

- `disbursement_date` required (d/m/Y → Y-m-d)
- `amount_disbursed` required numeric ≥1
- If cheque: `cheques[]` array with `{cheque_name, cheque_number, cheque_date, cheque_amount}`. Sum of cheque amounts must be ≤ disbursement amount.
- `bank_account_number` max 50

**Locked** when loan status ≠ active/on_hold.

## Remarks

- `GET /loans/{id}/remarks?stage_key=` — JSON list
- `POST /loans/{id}/remarks` — add (body: `remark`, optional `stage_key`)

`stage_key` nullable → general remark. `Remark::scopeForStage()` returns remarks for that stage OR general (null). `RemarkService::addRemark()` logs a truncated preview.

## Loan listing (DataTable)

`GET /loans/data` — server-side DataTables. Filter fields:

- `status` (default "active"), `customer_type`, `bank_id`, `branch_id`, `stage`, `role` (admin/mgr only — filters by who currently owns the loan)
- `docket` — overdue / due_today / due_soon / due_15 / due_month / custom (with custom date)
- `date_from` / `date_to`

Search: `loan_number`, `customer_name`, `bank_name`, `customer_phone`, `customer_email`.

Results include formatted amount, docket urgency badges, stage badge (with role suffix), owner info, actions (edit/delete per permission).

## Branch manager / BDH notes

- `view_all_loans` is **not** the default for BM/BDH. They see branch loans via the `scopeVisibleTo` OR-union (user_branches pivot).
- Only **super_admin** or users explicitly granted `view_all_loans` see cross-branch data.

## See also

- `workflow-developer.md` — stage engine internals
- `workflow-guide.md` — user-facing stage walkthrough
- `user-assignment.md` — how users get bound to stages
- `.claude/services-reference.md` — `LoanConversionService`, `LoanStageService`, `LoanDocumentService`, `DisbursementService`, `LoanTimelineService`, `RemarkService`
