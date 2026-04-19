# Services Reference

14 services in `app/Services/`. Orchestrate domain logic; called from controllers, never from views. Services are constructor-injected via Laravel's container (no explicit binding).

## ConfigService

Reads/writes `app_config` table (key `main`) and merges with `config/app-defaults.php`.

| Method | Signature | Notes |
|---|---|---|
| `load` | `(): array` | Returns merged config; seeds from defaults if row missing |
| `save` | `(array $config): void` | Upserts `app_config.main` |
| `reset` | `(): array` | Overwrites DB with `config('app-defaults')` |
| `get` | `(string $key, $default = null)` | Dot-notation read (e.g., `iomCharges.fixedCharge`) |
| `updateSection` | `(string $section, $value): array` | Dot-notation write + save |
| `updateMany` | `(array $updates): array` | Batch dot-notation writes + single save |

### Merge behavior

`mergeWithDefaults()` uses `array_replace_recursive($defaults, $loaded)`, then `replaceSequentialArrays()` walks the merged tree and **entirely replaces any sequential (indexed) array** with the DB value. Result:

- **Assoc arrays** (e.g., `iomCharges.*`): merged per key. New default keys appear even if not in DB.
- **Sequential arrays** (e.g., `banks`, `documents_en.proprietor`, `tenures`): replaced from DB, so UI deletions are respected.

### Double-encode pitfall

`AppConfig.config_json` is cast to `array`. Always pass raw arrays to `updateSection`/`updateMany`; never `json_encode()` yourself — the cast handles serialization.

---

## PermissionService

3-tier resolution for `User->hasPermission($slug)`:

1. `$user->hasRole('super_admin')` → `true`
2. User-specific override in `user_permissions` → `grant`/`deny`
3. Any `role_permission` row across user's roles → `true`; else `false`

| Method | Signature | Notes |
|---|---|---|
| `userHasPermission` | `(User, string): bool` | Main entry |
| `userRolesHavePermission` | `(User, string): bool` | Only checks role-level |
| `getUserPermissions` | `(User): array` | `[slug => bool]` for all permissions |
| `getGroupedPermissions` | `(): array` | `[group => [Permission,...]]` |
| `allSlugs` | `(): array` | Returns all permission slugs; cached 1h; used by `Gate::before` so `@can('slug')` / `$user->can('slug')` resolve through this service |
| `clearUserCache` | `(User): void` | Call after user roles or overrides change |
| `clearRoleCache` | `(): void` | Call after any role_permission change |
| `clearAllCaches` | `(): void` | After bulk edits or permission schema change; also forgets `all_permission_slugs` |

### Cache

| Key | TTL | Populated by |
|---|---|---|
| `user_perms:{userId}` | 300s (5 min) | `getUserOverride()` — maps slug → grant/deny for that user |
| `user_role_ids:{userId}` | 300s (5 min) | `getUserRoleIds()` — role IDs for a user |
| `role_perms:{sortedRoleIds}` | 300s (5 min) | `getRolePermissionSlugs()` — unique slugs across the comma-joined, sorted role-id set |
| `all_permission_slugs` | 3600s (1 hour) | `allSlugs()` — all permission slugs; returns `[]` quietly on table error (mid-migration safe) |

---

## QuotationService

Constructor: `ConfigService`, `PdfGenerationService`.

### `generate(array $input, int $userId): array`

Validates input, renders PDF, persists Quotation + QuotationBank + QuotationEmi + QuotationDocument, updates `bank_charges` for latest reference.

Validation rules (inline in service):
- `customerName`, `customerType`, `loanAmount` required
- `loanAmount` ≤ 10^12
- `banks[]` required array
- Per bank: `roiMin`, `roiMax` in (0, 30], `roiMin ≤ roiMax`

Additional accepted inputs (optional, passed through to template/persistence):
- `location_id`, `branch_id` — persisted on the Quotation row
- `selectedTenures` — int array; intersected with config `tenures` before use
- `ourServices` — free-text; defaults to config `ourServices` if absent
- `preparedByName`, `preparedByMobile` — persisted on the Quotation row

Return shapes:
- Success: `['success' => true, 'quotation' => Quotation]`
- Validation/error: `['error' => string]`
- PDF generated but DB failed: `['success' => false, 'error' => string, 'filename' => string]` (PDF still usable)

### `private updateBankCharges(array $banks): void`

Upserts `bank_charges` by `bank_name` with the last-used charge values for future pre-fill.

---

## PdfGenerationService

Three-tier fallback:

1. If `app.pdf_use_microservice=true` → microservice only
2. Else try Chrome headless (if `isChromeAvailable()`) → fallback to microservice on failure
3. Else microservice only

| Method | Signature | Notes |
|---|---|---|
| `generate` | `(array $data): array` | Renders HTML via `renderHtml()`, writes it to `storage/app/tmp/pdf_{uniqid}.html`, produces PDF at `storage/app/pdfs/Loan_Proposal_{Name}_{date}_{time}.pdf`. Returns `['success' => true, 'filename' => ..., 'path' => ...]` or `['error' => string]`. |
| `renderHtml` | `(array $data): string` | Builds the full bilingual HTML document (fonts, colors, charges, EMI comparison, documents, notes). Called internally by `generate()`; also usable standalone for previews. |
| `getTypeLabel` | `(string $type): string` *(static)* | Bilingual customer-type label (e.g. `proprietor` → `Proprietor / માલિકી`). Returns the raw key if unknown. |

Config keys read:
- `app.pdf_use_microservice`
- `app.chrome_path` (auto-detected from common Win/Linux/macOS paths if empty)
- `app.pdf_service_url` (default `http://127.0.0.1:3000/pdf`)
- `app.pdf_service_key` (sent as `X-API-Key` if set)

Chrome command flags: `--headless --disable-gpu --no-sandbox --run-all-compositor-stages-before-draw --print-to-pdf=... --no-pdf-header-footer --user-data-dir=...`. Temp user-data dir is cleaned after each run.

---

## NumberToWordsService

Static-style helpers for Indian numbering + bilingual words.

| Method | Signature |
|---|---|
| `toEnglish` | `(int): string` — "Twelve Lakh ... Rupees" |
| `toGujarati` | `(int): string` — "... રૂપિયા" |
| `toBilingual` | `(int): string` — "English / Gujarati" |
| `formatIndianNumber` | `($num): string` — "12,34,567" |
| `formatCurrency` | `($num): string` — "₹ 12,34,567" |

---

## LoanConversionService

Constructor: `LoanStageService`, `LoanDocumentService`.

### `convertFromQuotation(Quotation, int $bankIndex, array $extra = []): LoanDetail`

Inside DB transaction:
1. Guard if already converted
2. Create Customer from quotation + extras
3. Build `LoanDetail` (status=active, current_stage=document_collection)
4. `generateLoanNumber()` → `SHF-YYYYMM-NNNN`
5. Freeze `workflow_config` via `LoanStageService::buildWorkflowSnapshot()`
6. Populate documents via `LoanDocumentService::populateFromQuotation`
7. `initializeStages` → all stage_assignments
8. `autoCompleteStages(['inquiry','document_selection'])`
9. Auto-assign `document_collection` stage
10. Log `convert_quotation_to_loan` activity

### `createDirectLoan(array $data): LoanDetail`

Similar flow but starts at `inquiry`; documents pulled from `ConfigService` defaults by customer type.

---

## LoanStageService (workflow engine)

No injected deps. Talks directly to Stage, StageAssignment, StageTransfer, BankStageConfig, ProductStage, Bank, User, Branch, LoanProgress.

### Role resolution

| Method | Purpose |
|---|---|
| `getStageRoleEligibility(string): array` (static) | Reads `Stage.default_role` |
| `getAllStageRoleEligibility(): array` (static) | Cached map of all stages |
| `resolveStageRole(string, ?int $bankId): string` | bank override → stage default → `task_owner` |
| `resolvePhaseRole(string, int $phaseIndex, ?int $bankId): string` | bank override → `Stage.sub_actions[i].role` → `task_owner` |
| `buildWorkflowSnapshot(?bankId, ?productId, ?branchId, ?locationId): array` | Returns nested `{stage_key: {role, default_user_id, phases: {idx: {role, default_user_id}}}}`, frozen at loan creation |
| `getLoanStageRole(LoanDetail, string): string` | Reads from frozen snapshot, falls back to live |
| `getLoanPhaseRole(LoanDetail, string, int): string` | Same, for phases |
| `findUserForRole(string, LoanDetail, string, ?int $phaseIndex = null): ?int` | Snapshot default → role-specific resolution (task_owner → advisor/creator; bank_employee → bank default for city; office_employee → branch default) |

### Stage queries

`getOrderedStages()`, `getStageByKey($key)`, `getSubStages($parentKey)`, `isParallelStage($key)`, `getMainStageKeys()`.

### Initialization

- `initializeStages(LoanDetail)` — creates all `stage_assignments` + `loan_progress`
- `autoCompleteStages(LoanDetail, array $keys)` — bulk-completes given stages; used on conversion

### Transitions

- `updateStageStatus(LoanDetail, string, string, ?int $userId): StageAssignment` — validates via `StageAssignment::canTransitionTo()`, blocks on pending queries, runs `handleStageCompletion()` post-update
- `revertStageIfIncomplete(LoanDetail, string, bool $isStillComplete): bool` — soft-revert when collected data becomes incomplete; reverts subsequent stages too
- `getNextStage(string): ?string` — next main stage by sequence_order
- `canStartStage(LoanDetail, string): bool` — prerequisite checker — behavior branches on `app.open_rate_pf_parallel`; see Feature flag subsection below.
- `checkParallelCompletion(LoanDetail): bool` — marks `parallel_processing` parent complete when all its sub-stages are `completed`/`skipped`. Flag-off: auto-advances to `rate_pf` (assigns + starts it). Flag-on: calls `advanceToSanctionIfReady()`. Recalculates progress.
- `getParallelSubStages(LoanDetail): Collection` — returns all sub-stage assignments of `parallel_processing` with eager-loaded `stage` and `assignee`.
- `getLoanStageStatus(LoanDetail): Collection` — returns every `StageAssignment` for the loan with eager-loaded `stage`/`assignee`, sorted by `stage.sequence_order` (then `stage.id`). Used by stage UI to render in workflow order.

### `handleStageCompletion(LoanDetail, string)` (protected)

Orchestration logic:
- **app_number** done → start `bsm_osv` only
- **bsm_osv** done → start remaining parallel subs (legal_verification, technical_valuation, sanction_decision); if `config('app.open_rate_pf_parallel')` is truthy, also call `openRatePfInParallel()`
- All parallel subs done → mark `parallel_processing` complete; flag off → advance to `rate_pf`; flag on → call `advanceToSanctionIfReady()`
- **rate_pf** done (flag on only) → intercepted at top; call `advanceToSanctionIfReady()` and return
- **sanction** done → compute `expected_docket_date` from app_number stage notes (custom_docket_date OR docket_days_offset)
- **disbursement** (fund_transfer) → skip `otc_clearance`, mark loan `completed`
- **otc_clearance** done → mark loan `completed`
- Sequential advance + auto-assign next stage otherwise

### Feature flag: `open_rate_pf_parallel`

| Method | Purpose |
|---|---|
| `usesParallelRatePf(): bool` (private) | Reads `config('app.open_rate_pf_parallel')` |
| `openRatePfInParallel(LoanDetail): void` (public) | After bsm_osv completes (flag on), marks `rate_pf` in_progress, auto-assigns via `getLoanStageRole` + `findUserForRole`, writes StageTransfer row |
| `advanceToSanctionIfReady(LoanDetail): void` (public) | Opens `sanction` only when BOTH `parallel_processing` and `rate_pf` are completed/skipped. Called from `handleStageCompletion('rate_pf')` and `checkParallelCompletion()` |

`canStartStage()` branches on the flag: `rate_pf` opens after `bsm_osv` when on (not gated by `is_sanctioned`); `sanction` gate requires both `parallel_processing` and `rate_pf` complete when on. Legacy behavior preserved when off.

### Assignment & transfer

- `assignStage(LoanDetail, string, int $userId)` — manual assign
- `skipStage(LoanDetail, string, ?int $userId)` — marks skipped
- `autoAssignStage(LoanDetail, string): ?StageAssignment` — uses `findBestAssignee()`
- `autoAssignParallelSubStages(LoanDetail)` — only starts `app_number` first; rest wait
- `findBestAssignee(stageKey, branchId, bankId, productId, creatorId, advisorId): ?int` — priority: product_stage_users → advisor → bank default per city → bank employee per branch → any bank employee → default OE for branch → creator → fallback role match
- `transferStage(LoanDetail, string, int $toUserId, ?string $reason)` — updates assignment, creates StageTransfer, reassigns open queries

### Rejection

`rejectLoan(LoanDetail, string $stageKey, string $reason, ?int $userId): LoanDetail` — rejects only the named stage: sets loan status=rejected, writes `rejected_at`/`rejected_by`/`rejected_stage`/`rejection_reason`, closes that one stage assignment (saves `previous_status` then sets `status=rejected`). Sibling rejection for parallel-mode flows lives in `LoanStageController::sanctionDecisionAction` (lines 835-845), which bulk-updates pending/in_progress stages and saves `previous_status` for reactivation.

### Progress

`recalculateProgress(LoanDetail): LoanProgress` — rebuilds counts + workflow_snapshot.

---

## LoanDocumentService

Constructor: `ConfigService`.

| Method | Purpose |
|---|---|
| `populateFromQuotation(LoanDetail, Quotation)` | Copies quotation documents as pending |
| `populateFromDefaults(LoanDetail)` | Reads config `documents_en` / `documents_gu` by customer_type |
| `updateStatus(LoanDocument, string $status, int $userId, ?string $rejectedReason)` | pending / received / rejected / waived; sets received_date/by when received |
| `getProgress(LoanDetail): array` | `{total, resolved, received, rejected, pending, percentage}` |
| `allRequiredResolved(LoanDetail): bool` | Gate for auto-completing document_collection stage |
| `addDocument(LoanDetail, string $en, ?string $gu, bool $required = true): LoanDocument` | Adds custom doc with next sort_order |
| `removeDocument(LoanDocument)` | Deletes file too |
| `uploadFile(LoanDocument, UploadedFile, int $userId): LoanDocument` | Stores under `loan-documents/{loanId}/` via `FileUploadService::hashedFilename($file)` (random hash + ext) on the `local` disk; records `file_path`, `file_name` (original), `file_size`, `file_mime`, `uploaded_by`, `uploaded_at`; auto-marks document received if still pending |
| `deleteFile(LoanDocument)` | Removes file only; keeps record |

---

## DisbursementService

Constructor: `LoanStageService`.

### `processDisbursement(LoanDetail, array $data): DisbursementDetail`

Inside DB transaction:
1. Upsert `disbursement_details` for loan
2. If `disbursement_type=fund_transfer`: refresh relationships so `handleStageCompletion` can detect & skip OTC
3. Mark `disbursement` stage completed → triggers stage service completion flow
4. Log activity (action: `process_disbursement`; properties: `loan_number`, `type`, `amount`)
5. If loan completed, notify creator + advisor

---

## FileUploadService

Central upload validation + filename sanitization. All methods are `public static`; no constructor. Returns sanitized hashed filenames so client-supplied names never touch disk.

| Method | Signature | Notes |
|---|---|---|
| `rules` | `(bool $required = true): array` | Returns Laravel validator rules: `['required'\|'nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp', 'mimetypes:application/pdf,image/jpeg,image/png,image/webp']`. Both `mimes` (extension) and `mimetypes` (content) checks are enforced — extension alone is spoofable. |
| `messages` | `(): array` | Custom error messages for `file.mimes` / `file.mimetypes` / `file.max`. |
| `hashedFilename` | `(UploadedFile $file): string` | Returns `"{bin2hex(random_bytes(16))}.{ext}"`. Extension is lowercased and whitelist-checked; unknown extensions collapse to `bin`. Does **not** write the file — caller uses `$file->storeAs($dir, $name, 'local')`. |

### Constants

- `MAX_SIZE_KB = 10240` (10 MB)
- `ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp']`
- `ALLOWED_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp']`

### Storage

Consumers (e.g. `LoanDocumentService::uploadFile`) persist to the `local` disk, which resolves to `storage/app/private/` under the Laravel 11+ default filesystem config. The service itself is storage-agnostic — it only vends filenames + validation rules.

---

## StageQueryService

| Method | Purpose |
|---|---|
| `raiseQuery(StageAssignment, string $text, int $userId): StageQuery` | Creates query (status=pending); notifies stage assignee |
| `respondToQuery(StageQuery, string $text, int $userId): QueryResponse` | Appends response, sets query status=responded; notifies raiser |
| `resolveQuery(StageQuery, int $userId): StageQuery` | status=resolved + timestamps |
| `getQueriesForStage(StageAssignment): Collection` | All queries for a stage assignment |

Pending/responded queries **block stage completion** via a check inside `LoanStageService::updateStageStatus()`.

---

## RemarkService

| Method | Purpose |
|---|---|
| `addRemark(int $loanId, int $userId, string $remark, ?string $stageKey = null): Remark` | Logs activity w/ preview |
| `getRemarks(int $loanId, ?string $stageKey = null): Collection` | If stageKey set, filters `stage_key = $key OR NULL` (general + stage) |

---

## NotificationService

| Method | Purpose |
|---|---|
| `notify(int $userId, string $title, string $msg, string $type='info', ?int $loanId, ?string $stageKey, ?string $link): ShfNotification` | Generic. Auto-fallback: if `$link` is null and `$loanId` is passed, the link is resolved to `route('loans.stages', $loanId)` (wrapped in try/catch — stays null if route is unavailable). Callers can still pass an explicit `$link` to override (e.g. general-tasks point to the task page). |
| `notifyStageAssignment(LoanDetail, string $stageKey, int $userId): ShfNotification` | Title `"Stage Assigned"`, message `"You have been assigned to '{stageName}' for Loan #{loan_number} ({customer_name})"`, type `assignment`, link `route('loans.stages', $loan)`. `{stageName}` is `Stage.stage_name_en` (falls back to `stageKey`). |
| `notifyStageCompleted(LoanDetail, string): void` | Sent to creator + advisor (excluding current user) |
| `notifyLoanCompleted(LoanDetail): void` | Same audience |
| `markRead(ShfNotification): void` | |
| `markAllRead(int $userId): void` | |
| `getUnreadCount(int $userId): int` | |

UI polls `/api/notifications/count` every 60s (see `layouts/app.blade.php`).

### Daily reminders

`reminders:send-daily --when=morning|evening` (Artisan command `SendDailyReminders`) iterates users with pending work for today (morning, scheduled 08:00) or tomorrow (evening, scheduled 20:00):
- DVR follow-ups: `follow_up_needed=true`, `is_follow_up_done=false`, `follow_up_date = targetDate`, grouped by `user_id`
- General tasks: `status IN (pending, in_progress)`, `due_date = targetDate`, grouped by `assigned_to`

Users with zero in both buckets get no notification. Each recipient gets one in-app `ShfNotification` with a combined count ("You have N DVR follow-ups and M tasks due today/tomorrow.").

---

## LoanTimelineService

### `getTimeline(LoanDetail): Collection`

Merges 9+ event types into a single chronological collection (each entry: `{type, date, title, description, user, icon, color}`):
- `quotation_created` (if converted)
- `converted` (if from quotation — "Converted to Loan")
- `loan_created` (if direct)
- `stage_started` / `stage_completed` / `stage_skipped` (from `stage_assignments`)
- `transfer` (from `stage_transfers`)
- `query_raised` / `query_response` (from `stage_queries` + their responses)
- `remark` (from `remarks`)
- `rejected` (if loan status=rejected — "Loan Rejected")
- `disbursement` (if disbursement row exists — "Disbursement Processed")
- `completed` (if loan status=completed — "Loan Completed")

---

## Conventions

- **Transactions**: `convertFromQuotation`, `createDirectLoan`, `processDisbursement`, `generate` (DB-save phase) — wrapped in `DB::transaction`.
- **Activity logs**: services log via `ActivityLog::log($action, $subject, $properties)` after write.
- **Notifications**: sent inside the same request; no queue.
- **Cache invalidation**: `PermissionService` caches are the only service-level cache; `Role::clearAdvisorCache()` for advisor-eligible lookups.
- **Validation**: services trust inputs validated by controllers; `QuotationService::generate` is the only exception — it re-validates because it's also called by the offline sync API.
