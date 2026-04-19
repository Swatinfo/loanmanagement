# Models

34 Eloquent models in `app/Models/`. Traits, casts, relationships, and key methods per model.

For table-level definitions (columns, FKs, indexes) see `.claude/database-schema.md`.

## Common traits

| Trait | Source | Effect |
|---|---|---|
| `HasAuditColumns` | `App\Models\Traits` | Auto-fills `updated_by` (and `deleted_by` on soft-delete) from `auth()->id()` |
| `SoftDeletes` | Laravel | `deleted_at` column; rows hidden from default queries |
| `HasFactory` | Laravel | Factory support (only User currently has a factory) |
| `Impersonate` | lab404/laravel-impersonate | Impersonation methods on User |
| `Notifiable` | Laravel | Notification system (used by User) |

## By domain

### Core

**User** — extends `Authenticatable`. Uses `HasFactory`, `HasPushSubscriptions` (NotificationChannels\WebPush), `Impersonate`, `Notifiable`. Fillable: `name`, `email`, `password`, `is_active`, `created_by`, `phone`, `employee_id`, `default_branch_id`, `task_bank_id`. Casts: `email_verified_at => datetime`, `password => hashed`, `is_active => boolean`.

Relationships: `roles`, `userPermissions`, `branches` (pivot `user_branches` with only `is_default_office_employee`), `defaultBranch`, `taskBank`, `employerBanks` (pivot `bank_employees` with `is_default`, `location_id`), `locations`, `creator`, `createdUsers`.

Key methods: `hasRole(slug)`, `hasAnyRole([slugs])`, `isSuperAdmin()`, `isAdmin()`, `isBankEmployee()`, `isLoanAdvisor()`, `hasWorkflowRole()`, `canCreateLoans()`, `hasPermission(slug)` (delegates to `PermissionService`), `canImpersonate()`, `canBeImpersonated()`.

Static: `User::advisorEligibleRoles()` — returns cached list of role slugs with `can_be_advisor=true` (delegates to `Role::advisorEligibleSlugs()`).

Accessors: `getRoleLabelAttribute`, `getWorkflowRoleLabelAttribute`, `getWorkflowRoleLabelGuAttribute`, `getRoleSlugsAttribute`. Scope: `scopeAdvisorEligible`.

**Role** — Fillable `name`, `slug`, `description`, `can_be_advisor`, `is_system`. Casts: `can_be_advisor`, `is_system` → boolean. Relationships: `users`, `permissions`. Scopes: `scopeAdvisorEligible`, `scopeWorkflow` (non-system). `booted()` hook: on save/delete clears `PermissionService` caches and `clearAdvisorCache()`.

Static: `advisorEligibleSlugs()` (5-min cache), `clearAdvisorCache()`, `gujaratiLabels()`.

**Permission** — Fillable `name`, `slug`, `group`, `description`. Relationships: `roles`, `userPermissions`. `booted()` hook: on save/delete clears all `PermissionService` caches.

**UserPermission** — pivot-like row for per-user grant/deny. Fields: `user_id`, `permission_id`, `type` (grant/deny). Relationships: `user`, `permission`. `booted()` hook: on save/delete clears the affected user's `PermissionService` cache.

**Branch** — Uses `HasAuditColumns`, `SoftDeletes`. Relationships: `location`, `users` (pivot `user_branches`), `manager`. Scope: `scopeActive`.

**Location** — self-referential hierarchy: `parent_id` → state contains cities. Scopes: `scopeActive`, `scopeStates`, `scopeCities`. Methods: `isState()`, `isCity()`. Pivot rels: `users`, `products`; plus `branches` (HasMany).

**Bank** — Uses `HasAuditColumns`, `SoftDeletes`. Relationships: `products` (HasMany), `employees` (pivot `bank_employees` with `is_default`, `location_id`), `stageConfigs` (HasMany), `locations` (pivot `bank_location`). Method: `getDefaultEmployeeForCity(?cityId): ?userId`.

**BankCharge** — Per-bank PF + charges snapshot from latest quotation. Fields like `pf` (decimal 5,2), `admin`, `stamp_notary`, `registration_fee`, etc. Uses `$casts` property style (not `casts()` method).

**Product** — Uses `HasAuditColumns`, `SoftDeletes`. Relationships: `bank`, `stages` (pivot `product_stages` with flags), `productStages` (HasMany — access to full row), `locations` (pivot). Scope: `scopeActive`.

**Customer** — Uses `HasAuditColumns`, `SoftDeletes`. Fields: `customer_name`, `mobile`, `email`, `date_of_birth` (date cast), `pan_number`. Relationship: `loans` (HasMany).

### Workflow

**Stage** — master stage definition. Fillable: `stage_key`, `is_enabled`, `stage_name_en`, `stage_name_gu`, `sequence_order`, `is_parallel`, `parent_stage_key`, `stage_type`, `description_en`, `description_gu`, `default_role`, `assigned_role`, `sub_actions`. Casts: `is_enabled => boolean`, `is_parallel => boolean`, `sequence_order => integer`, `default_role => array`, `sub_actions => array`. Scopes: `enabled`, `mainStages`, `subStagesOf($parentKey)`. Relationships: `children`, `parent`, `bankConfigs`. Methods: `isSubStage()`, `isParent()`, `isDecision()`.

**BankStageConfig** — per-bank override. Fields: `bank_id`, `stage_id`, `assigned_role`, `phase_roles` (array). Relationships: `bank`, `stage`.

**ProductStage** — per-product configuration. Fields incl. `is_enabled`, `default_assignee_role`, `default_user_id`, `auto_skip`, `allow_skip`, `sort_order`, `sub_actions_override` (array). Relationships: `product`, `stage`, `defaultUser`, `branchUsers` (HasMany `ProductStageUser`). Methods: `getUserForBranch(?branchId)`, `getUserForLocation(?branchId, ?cityId, ?stateId, ?phaseIndex)`.

**ProductStageUser** — `product_stage_id`, `branch_id`, `location_id`, `user_id`, `is_default`, `phase_index`. Relationships: `productStage`, `branch`, `user`.

**StageAssignment** — one row per (loan, stage_key). Uses `HasAuditColumns` trait. Fields: `loan_id`, `stage_key`, `assigned_to`, `status`, `previous_status`, `priority`, `started_at`, `completed_at`, `completed_by`, `is_parallel_stage`, `parent_stage_key`, `notes` (stored as json-ish text). Casts: `is_parallel_stage => boolean`, `started_at => datetime`, `completed_at => datetime`.

Constants: `STATUS_PENDING`, `STATUS_IN_PROGRESS`, `STATUS_COMPLETED`, `STATUS_REJECTED`, `STATUS_SKIPPED`; `STATUSES` array (`['pending', 'in_progress', 'completed', 'rejected', 'skipped']`); plus `STATUS_LABELS`, `PRIORITY_LABELS` (each entry has `label` + `color` keys).

Relationships: `loan`, `assignee`, `completedByUser`, `stage` (by `stage_key`), `transfers`, `queries`, `activeQueries` (WHERE status IN pending, responded).

Scopes: `scopePending`, `scopeInProgress`, `scopeCompleted`, `scopeForUser`, `scopeMainStages`, `scopeSubStagesOf`.

Methods: `isActionable()`, `canTransitionTo(newStatus)` (state machine), `hasPendingQueries()`, `getNotesData(): array`, `mergeNotesData(array): void`.

**StageTransfer** — ledger row. `public $timestamps = false` but `created_at` is cast to `datetime`. Fields: `stage_assignment_id`, `loan_id`, `stage_key`, `transferred_from`, `transferred_to`, `reason`, `transfer_type`. Relationships: `stageAssignment`, `loan`, `fromUser` (BelongsTo User on `transferred_from`), `toUser` (BelongsTo User on `transferred_to`).

**StageQuery** — two-way blocking queries. Constants: `STATUS_PENDING`, `STATUS_RESPONDED`, `STATUS_RESOLVED`. Relationships: `stageAssignment`, `loan`, `raisedByUser`, `resolvedByUser`, `responses`. Scopes: `scopePending`, `scopeActive`, `scopeResolved`.

**QueryResponse** — `public $timestamps = false` but `created_at` cast. Fields: `stage_query_id`, `response_text`, `responded_by`. Relationships: `stageQuery`, `respondedByUser`.

**LoanProgress** — 1:1 with loan. Fields: `total_stages`, `completed_stages`, `overall_percentage`, `estimated_completion`, `workflow_snapshot`. Casts: `total_stages => integer`, `completed_stages => integer`, `overall_percentage => decimal:2`, `estimated_completion => date`, `workflow_snapshot => array`. Relationship: `loan`.

### Loan & related

**LoanDetail** — the main loan record. Uses `HasAuditColumns`, `SoftDeletes`.

Fillable: `loan_number`, `quotation_id`, `customer_id`, `branch_id`, `bank_id`, `product_id`, `location_id`, `customer_name`, `customer_type`, `customer_phone`, `customer_email`, `date_of_birth`, `pan_number`, `loan_amount`, `status`, `is_sanctioned`, `current_stage`, `bank_name`, `roi_min`, `roi_max`, `total_charges`, `application_number`, `assigned_bank_employee`, `due_date`, `expected_docket_date`, `rejected_at`, `rejected_by`, `rejected_stage`, `rejection_reason`, `status_reason`, `status_changed_at`, `status_changed_by`, `created_by`, `assigned_advisor`, `notes`, `workflow_config`.

Constants: `STATUS_ACTIVE`, `STATUS_COMPLETED`, `STATUS_REJECTED`, `STATUS_CANCELLED`, `STATUS_ON_HOLD`; `STATUSES` array (all status strings); plus `STATUS_LABELS`, `CUSTOMER_TYPE_LABELS` (bilingual).

Relationships: `quotation`, `branch`, `bank`, `product`, `location`, `customer`, `creator`, `advisor`, `bankEmployee`, `statusChangedByUser`, `documents` (HasMany), `stageAssignments` (HasMany), `progress` (HasOne), `stageTransfers` (HasMany), `stageQueries` (HasMany), `remarks` (HasMany), `valuationDetails` (HasMany), `disbursement` (HasOne).

Scopes: `scopeActive`, `scopeVisibleTo($user)` — see `loans.md` for logic.

Accessors:
- `formattedAmount` — `₹ 12,34,567`
- `statusLabel`, `statusColor`
- `customerTypeLabel` — bilingual
- `currentStageName` — shows child stages when `parallel_processing` is active, with role suffix
- `currentOwner` — `advisor ?? creator`
- `timeWithCurrentOwner`, `totalLoanTime` — d/h/m format
- `stageBadgeHtml` — HTML for the stage badge (including parallel sub-badges)

Methods: `getStageAssignment(string $key): ?StageAssignment`, `isBasicEditLocked(): bool` (method only — no accessor; true if `app_number` stage completed; `$loan->is_basic_edit_locked` does NOT work), `getEditableStageKey()`, `canEditStage(string $stageKey)`.

Static: `generateLoanNumber()` → `SHF-YYYYMM-NNNN`, `roleSuffix(?string $roleSlug)`, `userRoleSlug(?User $user)`, `stageBadgeClass(string $stageKey)`.

**LoanDocument** — Uses `HasAuditColumns`. Constants: `STATUS_PENDING`, `STATUS_RECEIVED`, `STATUS_REJECTED`, `STATUS_WAIVED`; `STATUSES` array; `STATUS_LABELS` (each entry has `label` + `color` keys). Fillable: `loan_id`, `document_name_en`, `document_name_gu`, `is_required`, `status`, `received_date`, `received_by`, `rejected_reason`, `notes`, `sort_order`, `file_path`, `file_name`, `file_size`, `file_mime`, `uploaded_by`, `uploaded_at`. Relationships: `loan`, `receivedByUser`, `uploadedByUser`. Scopes: `scopeRequired`, `scopeReceived`, `scopePending`, `scopeRejected`, `scopeResolved`, `scopeUnresolved`. Methods: `hasFile()`, `formattedFileSize()`, `isReceived()`, `isPending()`, `isResolved()`.

**ValuationDetail** — Uses `HasAuditColumns`. Constants: `TYPE_PROPERTY`, `TYPE_VEHICLE`, `TYPE_BUSINESS`; `TYPES` label array (`property => 'Property'`, `vehicle => 'Vehicle'`, `business => 'Business'`); `PROPERTY_TYPES` (residential_bunglow, residential_flat, commercial, industrial, land, mixed). Relationship: `loan`.

**DisbursementDetail** — Uses `HasAuditColumns`. Constants: `TYPE_FUND_TRANSFER`, `TYPE_CHEQUE`; `TYPES` label array. `cheques` cast to array. Relationships: `loan`, `otcClearedByUser` (BelongsTo User on `otc_cleared_by`).

**Remark** — Fields: `loan_id`, `stage_key` (nullable → general), `user_id`, `remark`. Scopes: `scopeForStage($key)`, `scopeGeneral`.

### Quotations

**Quotation** — Uses `HasAuditColumns`, `SoftDeletes`. Fillable includes `customer_name`, `customer_type`, `loan_amount`, `pdf_filename`, `pdf_path`, `prepared_by_name`, `prepared_by_mobile`, `selected_tenures` (array). Uses `$casts` property style (not `casts()` method). Relationships: `user`, `banks` (HasMany QuotationBank), `documents` (HasMany), `loan`, `location`, `branch`.

Accessors: `isConverted` (bool), `formattedAmount`. Methods: `getTypeLabel(): string` — bilingual label (METHOD, not accessor; `$quotation->type_label` does NOT work — call `$quotation->getTypeLabel()`).

**QuotationBank** — per-bank rate + charge row. Uses `$casts` property style (not `casts()` method). Relationships: `quotation`, `emiEntries` (HasMany QuotationEmi).

**QuotationEmi** — table `quotation_emi` (singular). Fields: `tenure_years`, `monthly_emi`, `total_interest`, `total_payment`. Uses `$casts` property style (not `casts()` method).

**QuotationDocument** — `quotation_id`, `document_name_en`, `document_name_gu`.

### Tasks & DVR

**GeneralTask** — Constants: `STATUS_PENDING`, `STATUS_IN_PROGRESS`, `STATUS_COMPLETED`, `STATUS_CANCELLED`; `STATUSES` array; `PRIORITY_LOW`, `PRIORITY_NORMAL`, `PRIORITY_HIGH`, `PRIORITY_URGENT`; `PRIORITIES` array; plus `STATUS_LABELS` and `PRIORITY_LABELS` where each entry uses a `badge` key (CSS class like `shf-badge-gray`) — differs from `StageAssignment` which uses a `color` key. Relationships: `creator`, `assignee`, `loan`, `comments` (HasMany, latest first). Scopes: `scopeVisibleTo($user)`, `scopePending`. Accessors: `statusBadgeHtml`, `priorityBadgeHtml`, `isOverdue`. Methods: `isVisibleTo($user)`, `isEditableBy($user)` (creator only), `canChangeStatus($user)` (creator or assignee), `isDeletableBy($user)` (creator only).

**GeneralTaskComment** — `general_task_id`, `user_id`, `body`. Relationships: `task`, `user`.

**DailyVisitReport** — fields: `user_id`, `visit_date`, `contact_name`, `contact_phone`, `contact_type`, `purpose`, `notes`, `outcome`, `follow_up_needed`, `follow_up_date`, `follow_up_notes`, `is_follow_up_done`, `parent_visit_id`, `follow_up_visit_id`, `quotation_id`, `loan_id`, `branch_id`. Relationships: `user`, `branch`, `quotation`, `loan`, `parentVisit`, `followUpVisit`. Scopes: `scopeVisibleTo($user)`, `scopePendingFollowUps`, `scopeOverdueFollowUps`. Accessor: `isOverdueFollowUp`. Methods:
- `isVisibleTo($user)` — own record, or `view_all_dvr` permission, or BDH/branch_manager with matching branch
- `isEditableBy($user)` — requires `edit_dvr` permission AND own record, OR super_admin
- `isDeletableBy($user)` — requires `delete_dvr` permission (no ownership check, no super_admin bypass)
- `getVisitChain()` — walks parent/follow-up links to return the full chain

### Notifications, Activity, Config

**ShfNotification** — table `shf_notifications`. Constants: `TYPE_INFO`, `TYPE_SUCCESS`, `TYPE_WARNING`, `TYPE_ERROR`, `TYPE_STAGE_UPDATE`, `TYPE_ASSIGNMENT`. Relationships: `user` (BelongsTo), `loan` (BelongsTo LoanDetail on `loan_id`). Scopes: `scopeUnread`, `scopeForUser`, `scopeRecent`. `booted()` hook: on `created`, dispatches `App\Events\NotificationBroadcast` (Reverb live-bell) AND calls `$user->notify(new ShfPushNotification($notification))` to send a Web Push notification (via `HasPushSubscriptions`) — failures are logged but don't throw.

**ActivityLog** — extends `Spatie\Activitylog\Models\Activity`; rows live in the `activity_log` table. Real columns: `log_name`, `description`, `subject_type`, `subject_id`, `event`, `causer_type`, `causer_id`, `attribute_changes`, `properties`, `batch_uuid`, `ip_address`, `user_agent`. Backward-compat accessors (not real columns): `$log->action` → `description`, `$log->user_id` → `causer_id`. Relationships: `user()` BelongsTo `User` on `causer_id` (legacy alias); `causer()` BelongsTo inherited from Spatie (MorphTo). Legacy static helper: `log($action, ?Model $subject, ?array $props): self` — writes via Spatie's API and records `impersonator_id` in `properties` when the session is impersonated.

**AppConfig** — Table `app_config`. Fields: `config_key`, `config_json` (array cast). Primary record uses `config_key='main'`.

## Relationship cheat sheet

```
User ─┬─ roles ───── Role ─── permissions ─── Permission
      ├─ userPermissions ─ UserPermission ─ Permission
      ├─ branches ── Branch ── location ── Location
      ├─ employerBanks ─ Bank (via bank_employees pivot)
      └─ locations ── Location (via location_user)

Quotation ─┬─ banks ─ QuotationBank ─ emiEntries ─ QuotationEmi
           ├─ documents ─ QuotationDocument
           └─ loan ─ LoanDetail

LoanDetail ─┬─ quotation ─ Quotation
            ├─ customer ─ Customer
            ├─ bank / product / branch / location
            ├─ documents ─ LoanDocument
            ├─ stageAssignments ─ StageAssignment ─┬─ transfers ─ StageTransfer
            │                                      └─ queries ─ StageQuery ─ responses ─ QueryResponse
            ├─ progress ─ LoanProgress
            ├─ valuationDetails ─ ValuationDetail
            ├─ disbursement ─ DisbursementDetail
            └─ remarks ─ Remark

Stage ─┬─ children ─ Stage (parent_stage_key)
       ├─ bankConfigs ─ BankStageConfig
       └─ (accessed via Product.stages pivot) ─ ProductStage ─ branchUsers ─ ProductStageUser
```

## See also

- `.claude/database-schema.md` — table-level schema
- `.claude/services-reference.md` — services that use these models
- `loans.md`, `quotations.md`, `workflow-developer.md` for domain usage
