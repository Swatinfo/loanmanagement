<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanDetail extends Model
{
    use HasAuditColumns, SoftDeletes;

    const STATUS_ACTIVE = 'active';

    const STATUS_COMPLETED = 'completed';

    const STATUS_REJECTED = 'rejected';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_ON_HOLD = 'on_hold';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_ON_HOLD,
    ];

    const STATUS_LABELS = [
        'active' => ['label' => 'Active', 'color' => 'primary'],
        'completed' => ['label' => 'Completed', 'color' => 'success'],
        'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'secondary'],
        'on_hold' => ['label' => 'On Hold', 'color' => 'warning'],
    ];

    const CUSTOMER_TYPE_LABELS = [
        'proprietor' => 'Proprietor / માલિકી',
        'partnership_llp' => 'Partnership / LLP / ભાગીદારી',
        'pvt_ltd' => 'Pvt. Ltd. / પ્રા. લિ.',
        'salaried' => 'Salaried / પગારદાર',
    ];

    protected $fillable = [
        'loan_number', 'quotation_id', 'customer_id', 'branch_id', 'bank_id', 'product_id', 'location_id',
        'customer_name', 'customer_type', 'customer_phone', 'customer_email', 'date_of_birth', 'pan_number',
        'loan_amount', 'status', 'is_sanctioned', 'current_stage', 'bank_name', 'roi_min', 'roi_max',
        'total_charges', 'application_number', 'assigned_bank_employee',
        'due_date', 'expected_docket_date', 'rejected_at', 'rejected_by', 'rejected_stage', 'rejection_reason',
        'status_reason', 'status_changed_at', 'status_changed_by',
        'created_by', 'assigned_advisor', 'notes', 'workflow_config',
    ];

    protected function casts(): array
    {
        return [
            'loan_amount' => 'integer',
            'is_sanctioned' => 'boolean',
            'roi_min' => 'decimal:2',
            'roi_max' => 'decimal:2',
            'due_date' => 'date',
            'date_of_birth' => 'date',
            'expected_docket_date' => 'date',
            'rejected_at' => 'datetime',
            'status_changed_at' => 'datetime',
            'workflow_config' => 'array',
        ];
    }

    // Relationships

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_advisor');
    }

    public function bankEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_bank_employee');
    }

    public function statusChangedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LoanDocument::class, 'loan_id');
    }

    public function stageAssignments(): HasMany
    {
        return $this->hasMany(StageAssignment::class, 'loan_id');
    }

    public function progress(): HasOne
    {
        return $this->hasOne(LoanProgress::class, 'loan_id');
    }

    public function stageTransfers(): HasMany
    {
        return $this->hasMany(StageTransfer::class, 'loan_id');
    }

    public function stageQueries(): HasMany
    {
        return $this->hasMany(StageQuery::class, 'loan_id');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(Remark::class, 'loan_id');
    }

    public function valuationDetails(): HasMany
    {
        return $this->hasMany(ValuationDetail::class, 'loan_id');
    }

    public function disbursement(): HasOne
    {
        return $this->hasOne(DisbursementDetail::class, 'loan_id');
    }

    // Stage helpers

    public function getStageAssignment(string $key): ?StageAssignment
    {
        return $this->stageAssignments()->where('stage_key', $key)->first();
    }

    // Ownership

    public function getCurrentOwnerAttribute(): ?User
    {
        // Prefer the assigned advisor; fall back to loan creator.
        // Use the already-loaded `advisor` relation so list pages don't run
        // one extra query per loan.
        return $this->advisor ?? $this->creator;
    }

    public function getTimeWithCurrentOwnerAttribute(): string
    {
        $assignment = $this->stageAssignments
            ->where('stage_key', $this->current_stage)
            ->first();

        // For parallel_processing — use first active sub-stage
        if ($this->current_stage === 'parallel_processing' && ! $assignment?->assigned_to) {
            $assignment = $this->stageAssignments
                ->where('parent_stage_key', 'parallel_processing')
                ->where('status', 'in_progress')
                ->sortBy('stage_key')
                ->first();
        }

        if (! $assignment || ! $assignment->assigned_to) {
            return '—';
        }

        $lastTransfer = $assignment->transfers()
            ->where('transferred_to', $assignment->assigned_to)
            ->latest('created_at')
            ->first();

        $since = $lastTransfer?->created_at ?? $assignment->started_at ?? $assignment->created_at;

        if (! $since) {
            return '—';
        }

        $diff = $since->diff(now());
        $parts = [];
        if ($diff->d > 0) {
            $parts[] = $diff->d.'d';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h.'h';
        }
        $parts[] = $diff->i.'m';

        return implode(' ', $parts);
    }

    public function getTotalLoanTimeAttribute(): string
    {
        if (! $this->created_at) {
            return '—';
        }

        // For closed loans, measure to updated_at (when status changed)
        $endTime = in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_REJECTED, self::STATUS_CANCELLED, self::STATUS_ON_HOLD])
            ? ($this->status_changed_at ?? $this->updated_at)
            : now();

        $diff = $this->created_at->diff($endTime);
        $parts = [];
        if ($diff->days > 0) {
            $parts[] = $diff->days.'d';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h.'h';
        }
        $parts[] = $diff->i.'m';

        return implode(' ', $parts);
    }

    // Accessors

    public function getFormattedAmountAttribute(): string
    {
        return "₹\u{00A0}".$this->formatIndianNumber($this->loan_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_LABELS[$this->status]['color'] ?? 'secondary';
    }

    public function getCustomerTypeLabelAttribute(): string
    {
        return self::CUSTOMER_TYPE_LABELS[$this->customer_type] ?? $this->customer_type;
    }

    public function getCurrentStageNameAttribute(): string
    {
        static $stageNames = null;
        if ($stageNames === null) {
            $stageNames = Stage::pluck('stage_name_en', 'stage_key')->toArray();
        }

        $baseName = $stageNames[$this->current_stage] ?? $this->current_stage;

        // For parallel_processing — show active sub-stage names with role suffixes
        if ($this->current_stage === 'parallel_processing') {
            $activeSubs = $this->stageAssignments
                ->where('parent_stage_key', 'parallel_processing')
                ->where('status', 'in_progress');

            if ($activeSubs->isNotEmpty()) {
                $subNames = $activeSubs->map(function ($sa) use ($stageNames) {
                    $name = $stageNames[$sa->stage_key] ?? $sa->stage_key;
                    $suffix = self::roleSuffix(self::userRoleSlug($sa->assignee));

                    return "{$name} ({$suffix})";
                })->implode(', ');

                return "{$baseName} ({$subNames})";
            }

            return $baseName;
        }

        // For all other stages — show role suffix
        $assignment = $this->stageAssignments
            ->where('stage_key', $this->current_stage)
            ->first();

        if ($assignment?->assignee) {
            $suffix = self::roleSuffix(self::userRoleSlug($assignment->assignee));

            return "{$baseName} ({$suffix})";
        }

        return $baseName;
    }

    public static function roleSuffix(?string $roleSlug): string
    {
        return match ($roleSlug) {
            'bank_employee' => 'Bank Review',
            'office_employee' => 'Office',
            'bdh' => 'BDH',
            default => 'SHF',
        };
    }

    /**
     * Get the primary workflow role slug for a user (for display purposes).
     */
    public static function userRoleSlug(?User $user): string
    {
        if (! $user) {
            return 'SHF';
        }

        // Check new roles system first
        if ($user->relationLoaded('roles') && $user->roles->isNotEmpty()) {
            $priorityOrder = ['bank_employee', 'office_employee', 'bdh', 'branch_manager', 'loan_advisor'];
            foreach ($priorityOrder as $slug) {
                if ($user->roles->contains('slug', $slug)) {
                    return $slug;
                }
            }
        }

        // Fall back to first workflow role
        $workflowRole = $user->roles->whereNotIn('slug', ['super_admin', 'admin'])->first();

        return $workflowRole?->slug ?? 'loan_advisor';
    }

    public static function stageBadgeClass(string $stageKey): string
    {
        $classes = [
            'inquiry' => 'shf-badge-stage-inquiry',
            'document_selection' => 'shf-badge-stage-doc-selection',
            'document_collection' => 'shf-badge-stage-doc-collection',
            'parallel_processing' => 'shf-badge-stage-app-number',
            'app_number' => 'shf-badge-stage-app-number',
            'bsm_osv' => 'shf-badge-stage-bsm-osv',
            'legal_verification' => 'shf-badge-stage-legal',
            'technical_valuation' => 'shf-badge-stage-valuation',
            'property_valuation' => 'shf-badge-stage-valuation',
            'sanction_decision' => 'shf-badge-stage-sanction-decision',
            'rate_pf' => 'shf-badge-stage-rate-pf',
            'sanction' => 'shf-badge-stage-sanction',
            'docket' => 'shf-badge-stage-docket',
            'kfs' => 'shf-badge-stage-kfs',
            'esign' => 'shf-badge-stage-esign',
            'disbursement' => 'shf-badge-stage-disbursement',
            'otc_clearance' => 'shf-badge-stage-otc',
        ];

        return $classes[$stageKey] ?? 'shf-badge-gray';
    }

    /**
     * Get stage badge HTML for loan listing.
     * For parallel_processing: returns individual sub-stage badges (no parent label).
     * For other stages: returns a single badge with role suffix.
     */
    public function getStageBadgeHtmlAttribute(): string
    {
        static $stageNames = null;
        if ($stageNames === null) {
            $stageNames = Stage::pluck('stage_name_en', 'stage_key')->toArray();
        }

        if ($this->current_stage === 'parallel_processing') {
            $activeSubs = $this->stageAssignments
                ->where('parent_stage_key', 'parallel_processing')
                ->where('status', 'in_progress');

            if ($activeSubs->isEmpty()) {
                $baseName = $stageNames['parallel_processing'] ?? 'Parallel Processing';

                return '<span class="shf-badge shf-badge-stage-app-number shf-text-2xs">'.$baseName.'</span>';
            }

            return $activeSubs->map(function ($sa) use ($stageNames) {
                $name = $stageNames[$sa->stage_key] ?? $sa->stage_key;
                $suffix = self::roleSuffix(self::userRoleSlug($sa->assignee));
                $cssClass = self::stageBadgeClass($sa->stage_key);

                return '<span class="shf-badge '.$cssClass.' shf-text-2xs">'.$name.' ('.$suffix.')</span>';
            })->implode(' ');
        }

        $baseName = $stageNames[$this->current_stage] ?? $this->current_stage;
        $cssClass = self::stageBadgeClass($this->current_stage);

        $assignment = $this->stageAssignments
            ->where('stage_key', $this->current_stage)
            ->first();

        if ($assignment?->assignee) {
            $suffix = self::roleSuffix(self::userRoleSlug($assignment->assignee));
            $baseName .= ' ('.$suffix.')';
        }

        return '<span class="shf-badge '.$cssClass.' shf-text-2xs">'.$baseName.'</span>';
    }

    // Edit restrictions

    /**
     * Whether the loan's basic details (customer info, bank, amount) can be edited.
     * Locked once application number sub-stage has been completed.
     */
    public function isBasicEditLocked(): bool
    {
        $appNumberAssignment = $this->stageAssignments
            ->where('stage_key', 'app_number')
            ->first();

        return $appNumberAssignment && $appNumberAssignment->status === 'completed';
    }

    /**
     * Get the stage key of the immediate previous stage that can be edited.
     * Returns null if no previous stage is editable.
     */
    public function getEditableStageKey(): ?string
    {
        $stageOrder = [
            'inquiry', 'document_selection', 'document_collection',
            'app_number', 'bsm_osv', 'legal_verification', 'technical_valuation',
            'rate_pf', 'sanction', 'docket', 'kfs', 'esign', 'disbursement', 'otc_clearance',
        ];

        $currentStage = $this->current_stage;

        // For parallel_processing, find the active sub-stages and allow editing app_number only if all subs are active
        if ($currentStage === 'parallel_processing') {
            $appNumber = $this->stageAssignments->where('stage_key', 'app_number')->first();
            if ($appNumber && $appNumber->status === 'in_progress') {
                // app_number is active — previous is document_collection
                return 'document_collection';
            }

            // app_number completed, others active — previous is app_number
            return 'app_number';
        }

        $currentIdx = array_search($currentStage, $stageOrder);
        if ($currentIdx === false || $currentIdx === 0) {
            return null;
        }

        return $stageOrder[$currentIdx - 1];
    }

    /**
     * Whether a specific completed stage can be edited.
     */
    public function canEditStage(string $stageKey): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        return $this->getEditableStageKey() === $stageKey;
    }

    // Static Methods

    public static function generateLoanNumber(): string
    {
        $prefix = 'SHF-'.now()->format('Ym').'-';
        $lastLoan = static::where('loan_number', 'like', $prefix.'%')
            ->orderByDesc('loan_number')
            ->first();

        if ($lastLoan) {
            $lastNum = (int) substr($lastLoan->loan_number, -4);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix.str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    // Scopes

    public function scopeActive($query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeVisibleTo($query, User $user): void
    {
        // Super admin and admin with view_all_loans see everything
        if ($user->hasPermission('view_all_loans')) {
            return;
        }

        $query->where(function ($q) use ($user) {
            // Own loans (created or assigned as advisor)
            $q->where('created_by', $user->id)
                ->orWhere('assigned_advisor', $user->id)
                // Assigned to any stage (current or past)
                ->orWhereHas('stageAssignments', fn ($sq) => $sq->where('assigned_to', $user->id));

            // Branch managers and BDHs can see all loans in their branches
            if ($user->hasRole('branch_manager') || $user->hasRole('bdh')) {
                $branchIds = $user->branches()->pluck('branches.id')->toArray();
                if ($branchIds) {
                    $q->orWhereIn('branch_id', $branchIds);
                }
            }

            // Bank/office employees also see loans they've transferred from (stage_transfers history)
            if ($user->hasRole('bank_employee') || $user->hasRole('office_employee')) {
                $q->orWhereHas('stageTransfers', fn ($sq) => $sq->where('transferred_from', $user->id)->orWhere('transferred_to', $user->id));
            }
        });
    }

    // Helpers

    private function formatIndianNumber($num): string
    {
        $num = (int) $num;
        if ($num < 1000) {
            return (string) $num;
        }

        $lastThree = $num % 1000;
        $remaining = (int) ($num / 1000);
        $result = '';

        if ($remaining > 0) {
            $remainStr = (string) $remaining;
            $len = strlen($remainStr);
            $groups = [];
            $i = $len;
            while ($i > 0) {
                $start = max(0, $i - 2);
                $groups[] = substr($remainStr, $start, $i - $start);
                $i = $start;
            }
            $result = implode(',', array_reverse($groups)).',';
        }

        $result .= str_pad($lastThree, 3, '0', STR_PAD_LEFT);

        return ltrim($result, '0,') ?: '0';
    }
}
