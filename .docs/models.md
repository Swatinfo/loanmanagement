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

**User** — extends `Authenticatable`. Uses `HasFactory`, `Impersonate`, `Notifiable`. Fillable includes `name`, `email`, `password`, `is_active`, `phone`, `employee_id`, `default_branch_id`, `task_bank_id`. `password` cast to `hashed`.

Relationships: `roles`, `userPermissions`, `branches` (pivot `user_branches`), `defaultBranch`, `taskBank`, `employerBanks` (pivot `bank_employees` with `is_default`, `location_id`), `locations`, `creator`, `createdUsers`.

Key methods: `hasRole(slug)`, `hasAnyRole([slugs])`, `isSuperAdmin()`, `isAdmin()`, `isBankEmployee()`, `isLoanAdvisor()`, `hasWorkflowRole()`, `canCreateLoans()`, `hasPermission(slug)` (delegates to `PermissionService`), `canImpersonate()`, `canBeImpersonated()`.

Accessors: `getRoleLabelAttribute`, `getWorkflowRoleLabelAttribute`, `getWorkflowRoleLabelGuAttribute`, `getRoleSlugsAttribute`. Scope: `scopeAdvisorEligible`.

**Role** — Fillable `name`, `slug`, `description`, `can_be_advisor`, `is_system`. Relationships: `users`, `permissions`. Scopes: `scopeAdvisorEligible`, `scopeWorkflow` (non-system).

Static: `advisorEligibleSlugs()` (5-min cache), `clearAdvisorCache()`, `gujaratiLabels()`.

**Permission** — Fillable `name`, `slug`, `group`, `description`. Relationships: `roles`, `userPermissions`.

**UserPermission** — pivot-like row for per-user grant/deny. Fields: `user_id`, `permission_id`, `type` (grant/deny). Relationships: `user`, `permission`.

**Branch** — Uses `HasAuditColumns`, `SoftDeletes`. Relationships: `location`, `users` (pivot `user_branches`), `manager`. Scope: `scopeActive`.

**Location** — self-referential hierarchy: `parent_id` → state contains cities. Scopes: `scopeActive`, `scopeStates`, `scopeCities`. Methods: `isState()`, `isCity()`. Pivot rels: `users`, `products`; plus `branches` (HasMany).

**Bank** — Uses `HasAuditColumns`, `SoftDeletes`. Relationships: `products` (HasMany), `employees` (pivot `bank_employees` with `is_default`, `location_id`), `stageConfigs` (HasMany), `locations` (pivot `bank_location`). Method: `getDefaultEmployeeForCity(?cityId): ?userId`.

**BankCharge** — Per-bank PF + charges snapshot from latest quotation. Fields like `pf` (decimal 5,2), `admin`, `stamp_notary`, `registration_fee`, etc.

**Product** — Uses `HasAuditColumns`, `SoftDeletes`. Relationships: `bank`, `stages` (pivot `product_stages` with flags), `productStages` (HasMany — access to full row), `locations` (pivot). Scope: `scopeActive`.

**Customer** — Uses `HasAuditColumns`, `SoftDeletes`. Fields: `customer_name`, `mobile`, `email`, `date_of_birth` (date cast), `pan_number`. Relationship: `loans` (HasMany).

### Workflow

**Stage** — master stage definition. Fields: `stage_key`, `is_enabled`, `stage_name_en/gu`, `sequence_order`, `is_parallel`, `parent_stage_key`, `stage_type`, `default_role` (array cast), `assigned_role`, `sub_actions` (array cast). Scopes: `enabled`, `mainStages`, `subStagesOf($parentKey)`. Relationships: `children`, `parent`, `bankConfigs`. Methods: `isSubStage()`, `isParent()`, `isDecision()`.

**BankStageConfig** — per-bank override. Fields: `bank_id`, `stage_id`, `assigned_role`, `phase_roles` (array). Relationships: `bank`, `stage`.

**ProductStage** — per-product configuration. Fields incl. `is_enabled`, `default_assignee_role`, `default_user_id`, `auto_skip`, `allow_skip`, `sort_order`, `sub_actions_override` (array). Relationships: `product`, `stage`, `defaultUser`, `branchUsers` (HasMany `ProductStageUser`). Methods: `getUserForBranch(?branchId)`, `getUserForLocation(?branchId, ?cityId, ?stateId, ?phaseIndex)`.

**ProductStageUser** — `product_stage_id`, `branch_id`, `location_id`, `user_id`, `is_default`, `phase_index`. Relationships: `productStage`, `branch`, `user`.

**StageAssignment** — one row per (loan, stage_key). Fields: `loan_id`, `stage_key`, `assigned_to`, `status`, `previous_status`, `priority`, `started_at`, `completed_at`, `completed_by`, `is_parallel_stage`, `parent_stage_key`, `notes` (stored as json-ish text).

Constants: `STATUS_PENDING`, `STATUS_IN_PROGRESS`, `STATUS_COMPLETED`, `STATUS_REJECTED`, `STATUS_SKIPPED`; plus `STATUS_LABELS`, `PRIORITY_LABELS`.

Relationships: `loan`, `assignee`, `completedByUser`, `stage` (by `stage_key`), `transfers`, `queries`, `activeQueries` (WHERE status IN pending, responded).

Scopes: `scopePending`, `scopeInProgress`, `scopeCompleted`, `scopeForUser`, `scopeMainStages`, `scopeSubStagesOf`.

Methods: `isActionable()`, `canTransitionTo(newStatus)` (state machine), `hasPendingQueries()`, `getNotesData(): array`, `mergeNotesData(array): void`.

**StageTransfer** — ledger row. `public $timestamps = false` but `created_at` is cast to `datetime`. Fields: `stage_assignment_id`, `loan_id`, `stage_key`, `transferred_from`, `transferred_to`, `reason`, `transfer_type`.

**StageQuery** — two-way blocking queries. Constants: `STATUS_PENDING`, `STATUS_RESPONDED`, `STATUS_RESOLVED`. Relationships: `stageAssignment`, `loan`, `raisedByUser`, `resolvedByUser`, `responses`. Scopes: `scopePending`, `scopeActive`, `scopeResolved`.

**QueryResponse** — `public $timestamps = false` but `created_at` cast. Fields: `stage_query_id`, `response_text`, `responded_by`. Relationships: `stageQuery`, `respondedByUser`.

**LoanProgress** — 1:1 with loan. Fields: `total_stages`, `completed_stages`, `overall_percentage`, `estimated_completion`, `workflow_snapshot` (array). Relationship: `loan`.

### Loan & related

**LoanDetail** — the main loan record. Uses `HasAuditColumns`, `SoftDeletes`.

Constants: `STATUS_ACTIVE`, `STATUS_COMPLETED`, `STATUS_REJECTED`, `STATUS_CANCELLED`, `STATUS_ON_HOLD`; plus `STATUS_LABELS`, `CUSTOMER_TYPE_LABELS` (bilingual).

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
- `isBasicEditLocked` — true if app_number stage completed

Methods: `getStageAssignment(string $key): ?StageAssignment`, `isBasicEditLocked()`, `getEditableStageKey()`, `canEditStage(string $stageKey)`.

Static: `generateLoanNumber()` → `SHF-YYYYMM-NNNN`, `roleSuffix(?string $roleSlug)`, `userRoleSlug(?User $user)`, `stageBadgeClass(string $stageKey)`.

**LoanDocument** — Uses `HasAuditColumns`. Constants: `STATUS_PENDING`, `STATUS_RECEIVED`, `STATUS_REJECTED`, `STATUS_WAIVED`. Relationships: `loan`, `receivedByUser`, `uploadedByUser`. Scopes: `scopeRequired`, `scopeReceived`, `scopePending`, `scopeRejected`, `scopeResolved`, `scopeUnresolved`. Methods: `hasFile()`, `formattedFileSize()`, `isReceived()`, `isPending()`, `isResolved()`.

**ValuationDetail** — Uses `HasAuditColumns`. Constants: `TYPE_PROPERTY`, `TYPE_VEHICLE`, `TYPE_BUSINESS`, `PROPERTY_TYPES` (residential_bunglow, residential_flat, commercial, industrial, land, mixed).

**DisbursementDetail** — Uses `HasAuditColumns`. Constants: `TYPE_FUND_TRANSFER`, `TYPE_CHEQUE`. `cheques` cast to array.

**Remark** — Fields: `loan_id`, `stage_key` (nullable → general), `user_id`, `remark`. Scopes: `scopeForStage($key)`, `scopeGeneral`.

### Quotations

**Quotation** — Uses `HasAuditColumns`, `SoftDeletes`. Fillable includes `customer_name`, `customer_type`, `loan_amount`, `pdf_filename`, `pdf_path`, `prepared_by_name`, `prepared_by_mobile`, `selected_tenures` (array). Relationships: `user`, `banks` (HasMany QuotationBank), `documents` (HasMany), `loan`, `location`, `branch`.

Accessors: `isConverted` (bool), `formattedAmount`, `typeLabel` (bilingual).

**QuotationBank** — per-bank rate + charge row. Relationships: `quotation`, `emiEntries` (HasMany QuotationEmi).

**QuotationEmi** — table `quotation_emi` (singular). Fields: `tenure_years`, `monthly_emi`, `total_interest`, `total_payment`.

**QuotationDocument** — `quotation_id`, `document_name_en`, `document_name_gu`.

### Tasks & DVR

**GeneralTask** — Constants for statuses (pending / in_progress / completed / cancelled) and priorities (low / normal / high / urgent) with labels. Relationships: `creator`, `assignee`, `loan`, `comments` (HasMany, latest first). Scopes: `scopeVisibleTo($user)`, `scopePending`. Accessors: `statusBadgeHtml`, `priorityBadgeHtml`, `isOverdue`. Methods: `isVisibleTo($user)`, `isEditableBy($user)` (creator only), `canChangeStatus($user)` (creator or assignee), `isDeletableBy($user)` (creator only).

**GeneralTaskComment** — `general_task_id`, `user_id`, `body`. Relationships: `task`, `user`.

**DailyVisitReport** — fields: `user_id`, `visit_date`, `contact_name`, `contact_phone`, `contact_type`, `purpose`, `notes`, `outcome`, `follow_up_needed`, `follow_up_date`, `follow_up_notes`, `is_follow_up_done`, `parent_visit_id`, `follow_up_visit_id`, `quotation_id`, `loan_id`, `branch_id`. Relationships: `user`, `branch`, `quotation`, `loan`, `parentVisit`, `followUpVisit`. Scopes: `scopeVisibleTo($user)`, `scopePendingFollowUps`, `scopeOverdueFollowUps`. Accessor: `isOverdueFollowUp`. Methods: `isVisibleTo`, `isEditableBy`, `isDeletableBy`, `getVisitChain()`.

### Notifications, Activity, Config

**ShfNotification** — table `shf_notifications`. Constants: `TYPE_INFO`, `TYPE_SUCCESS`, `TYPE_WARNING`, `TYPE_ERROR`, `TYPE_STAGE_UPDATE`, `TYPE_ASSIGNMENT`. Scopes: `scopeUnread`, `scopeForUser`, `scopeRecent`.

**ActivityLog** — Fields: `user_id`, `action`, `subject_type`, `subject_id`, `properties` (array cast), `ip_address`, `user_agent`. Static helper: `log($action, ?Model $subject, ?array $props): self`.

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
