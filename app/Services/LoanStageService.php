<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Bank;
use App\Models\BankStageConfig;
use App\Models\Branch;
use App\Models\LoanDetail;
use App\Models\LoanProgress;
use App\Models\ProductStage;
use App\Models\Stage;
use App\Models\StageAssignment;
use App\Models\StageQuery;
use App\Models\StageTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LoanStageService
{
    /**
     * Get eligible roles for a stage from the database.
     */
    public static function getStageRoleEligibility(string $stageKey): array
    {
        $stage = Stage::where('stage_key', $stageKey)->first();

        return $stage?->default_role ?? [];
    }

    /**
     * Get all stage role eligibility as a map [stage_key => roles[]] from the database.
     */
    public static function getAllStageRoleEligibility(): array
    {
        return Stage::whereNotNull('default_role')
            ->pluck('default_role', 'stage_key')
            ->toArray();
    }

    // ── Bank-Wise Role Resolution ──

    /**
     * Resolve the assigned role for a stage (single-phase stages).
     * Priority: bank_stage_configs → stages.assigned_role
     */
    public function resolveStageRole(string $stageKey, ?int $bankId): string
    {
        if ($bankId) {
            $stage = Stage::where('stage_key', $stageKey)->first();
            if ($stage) {
                $bankConfig = BankStageConfig::where('bank_id', $bankId)
                    ->where('stage_id', $stage->id)
                    ->first();
                if ($bankConfig && $bankConfig->assigned_role) {
                    return $bankConfig->assigned_role;
                }
            }
        }

        $stage = $stage ?? Stage::where('stage_key', $stageKey)->first();

        return $stage?->assigned_role ?? 'task_owner';
    }

    /**
     * Resolve the role for a specific phase of a multi-phase stage.
     * Priority: bank_stage_configs.phase_roles → stages.sub_actions[].role
     */
    public function resolvePhaseRole(string $stageKey, int $phaseIndex, ?int $bankId): string
    {
        $stage = Stage::where('stage_key', $stageKey)->first();
        if (! $stage) {
            return 'task_owner';
        }

        // Check bank override
        if ($bankId) {
            $bankConfig = BankStageConfig::where('bank_id', $bankId)
                ->where('stage_id', $stage->id)
                ->first();
            if ($bankConfig && is_array($bankConfig->phase_roles) && isset($bankConfig->phase_roles[(string) $phaseIndex])) {
                return $bankConfig->phase_roles[(string) $phaseIndex];
            }
        }

        // Fall back to master sub_actions
        $subActions = $stage->sub_actions;
        if (is_array($subActions) && isset($subActions[$phaseIndex]['role'])) {
            return $subActions[$phaseIndex]['role'];
        }

        return 'task_owner';
    }

    /**
     * Build a complete workflow config snapshot for a loan.
     * Captures role + default_user_id for every stage and phase.
     */
    public function buildWorkflowSnapshot(?int $bankId, ?int $productId = null, ?int $branchId = null, ?int $locationId = null): array
    {
        $stages = Stage::where('is_enabled', true)->get();
        $config = [];

        // Pre-load bank configs for this bank
        $bankConfigs = [];
        if ($bankId) {
            $bankConfigs = BankStageConfig::where('bank_id', $bankId)
                ->get()
                ->keyBy('stage_id')
                ->toArray();
        }

        // Pre-load product stages for user resolution
        $productStages = [];
        if ($productId) {
            $productStages = ProductStage::where('product_id', $productId)
                ->with('branchUsers')
                ->get()
                ->keyBy('stage_id');
        }

        // Resolve branch location info
        $branch = $branchId ? Branch::with('location.parent')->find($branchId) : null;
        $cityId = $locationId ?? $branch?->location_id;
        $stateId = $branch?->location?->parent_id;

        foreach ($stages as $stage) {
            $bankConfig = $bankConfigs[$stage->id] ?? null;
            $ps = $productStages[$stage->id] ?? null;

            // Resolve stage-level role
            $stageRole = $bankConfig['assigned_role'] ?? $stage->assigned_role ?? 'task_owner';

            // Resolve stage-level default user
            $stageDefaultUser = $this->resolveDefaultUserFromProductStage($ps, $stageRole, $branchId, $cityId, $stateId);

            $stageConfig = [
                'role' => $stageRole,
                'default_user_id' => $stageDefaultUser,
            ];

            // Resolve phases for multi-phase stages
            $subActions = $stage->sub_actions;
            if (is_array($subActions) && count($subActions) > 0) {
                $phases = [];
                $bankPhaseRoles = is_array($bankConfig['phase_roles'] ?? null) ? $bankConfig['phase_roles'] : [];

                foreach ($subActions as $idx => $sa) {
                    $phaseRole = $bankPhaseRoles[(string) $idx] ?? $sa['role'] ?? 'task_owner';
                    $phaseDefaultUser = $this->resolveDefaultUserFromProductStage($ps, $phaseRole, $branchId, $cityId, $stateId, $idx);

                    $phases[(string) $idx] = [
                        'role' => $phaseRole,
                        'default_user_id' => $phaseDefaultUser,
                    ];
                }
                $stageConfig['phases'] = $phases;
            }

            $config[$stage->stage_key] = $stageConfig;
        }

        return $config;
    }

    /**
     * Get the role for a stage from a loan's frozen snapshot.
     * Falls back to live resolution for loans without snapshots.
     */
    public function getLoanStageRole(LoanDetail $loan, string $stageKey): string
    {
        $config = $loan->workflow_config;
        if ($config && isset($config[$stageKey]['role'])) {
            return $config[$stageKey]['role'];
        }

        return $this->resolveStageRole($stageKey, $loan->bank_id);
    }

    /**
     * Get the role for a specific phase from a loan's frozen snapshot.
     * Falls back to live resolution for loans without snapshots.
     */
    public function getLoanPhaseRole(LoanDetail $loan, string $stageKey, int $phaseIndex): string
    {
        $config = $loan->workflow_config;
        if ($config && isset($config[$stageKey]['phases'][(string) $phaseIndex]['role'])) {
            return $config[$stageKey]['phases'][(string) $phaseIndex]['role'];
        }

        return $this->resolvePhaseRole($stageKey, $phaseIndex, $loan->bank_id);
    }

    /**
     * Find the best user for a resolved role on a loan.
     * Priority: snapshot default_user → product stage user → bank/branch defaults → fallback.
     */
    public function findUserForRole(string $role, LoanDetail $loan, string $stageKey, ?int $phaseIndex = null): ?int
    {
        // 1. Check snapshot for frozen default user
        $config = $loan->workflow_config;
        if ($config && isset($config[$stageKey])) {
            $snapshotUserId = null;
            if ($phaseIndex !== null && isset($config[$stageKey]['phases'][(string) $phaseIndex])) {
                $snapshotUserId = $config[$stageKey]['phases'][(string) $phaseIndex]['default_user_id'] ?? null;
            } else {
                $snapshotUserId = $config[$stageKey]['default_user_id'] ?? null;
            }

            // If no stage-level user but has phases, find the first phase matching the requested role
            if (! $snapshotUserId && $phaseIndex === null && isset($config[$stageKey]['phases'])) {
                foreach ($config[$stageKey]['phases'] as $pIdx => $phaseData) {
                    if (($phaseData['role'] ?? '') === $role && ! empty($phaseData['default_user_id'])) {
                        $snapshotUserId = $phaseData['default_user_id'];
                        break;
                    }
                }
            }

            if ($snapshotUserId) {
                $user = User::where('id', $snapshotUserId)->where('is_active', true)->first();
                if ($user) {
                    return $user->id;
                }
            }
        }

        // 2. Resolve by role type
        if ($role === 'task_owner') {
            return $loan->assigned_advisor ?? $loan->created_by;
        }

        if ($role === 'bank_employee') {
            return $this->findBankEmployeeForLoan($loan, $stageKey, $phaseIndex);
        }

        if ($role === 'office_employee') {
            return $this->findOfficeEmployeeForLoan($loan, $stageKey);
        }

        return null;
    }

    /**
     * Find best bank employee for a loan.
     * Priority: product stage user → bank default for city → any BE for bank.
     */
    private function findBankEmployeeForLoan(LoanDetail $loan, string $stageKey, ?int $phaseIndex = null): ?int
    {
        // Product stage user (phase-aware)
        if ($loan->product_id) {
            $stage = $this->getStageByKey($stageKey);
            if ($stage) {
                $productStage = ProductStage::where('product_id', $loan->product_id)
                    ->where('stage_id', $stage->id)->first();
                if ($productStage) {
                    $branch = $loan->branch_id ? Branch::with('location.parent')->find($loan->branch_id) : null;
                    $cityId = $branch?->location_id;
                    $stateId = $branch?->location?->parent_id;
                    $userId = $productStage->getUserForLocation($loan->branch_id, $cityId, $stateId, $phaseIndex);
                    if ($userId && User::where('id', $userId)->where('is_active', true)->exists()) {
                        return $userId;
                    }
                }
            }
        }

        // Bank default employee for city
        if ($loan->bank_id) {
            $cityId = $loan->branch_id ? Branch::find($loan->branch_id)?->location_id : null;
            $bank = Bank::find($loan->bank_id);
            if ($bank) {
                $defaultBEId = $bank->getDefaultEmployeeForCity($cityId);
                if ($defaultBEId && User::where('id', $defaultBEId)->where('is_active', true)->exists()) {
                    return $defaultBEId;
                }
            }
        }

        // Any active bank employee for this bank
        if ($loan->bank_id) {
            $user = User::where('is_active', true)
                ->whereHas('employerBanks', fn ($q) => $q->where('banks.id', $loan->bank_id))
                ->first();
            if ($user) {
                return $user->id;
            }
        }

        return null;
    }

    /**
     * Resolve default user from product stage config for snapshot building.
     * Returns null for task_owner roles (resolved at runtime from loan).
     */
    private function resolveDefaultUserFromProductStage(?object $productStage, string $role, ?int $branchId, ?int $cityId, ?int $stateId, ?int $phaseIndex = null): ?int
    {
        if ($role === 'task_owner') {
            return null; // Always resolved from loan's advisor/creator at runtime
        }

        if (! $productStage) {
            return null;
        }

        // Check for phase-specific user assignment
        if ($phaseIndex !== null && $productStage->branchUsers) {
            $phaseUser = $productStage->branchUsers
                ->where('phase_index', $phaseIndex)
                ->where('is_default', true)
                ->when($branchId, fn ($c) => $c->where(fn ($u) => $u->branch_id === $branchId || $u->branch_id === null))
                ->first();
            if ($phaseUser) {
                return $phaseUser->user_id;
            }
        }

        // Fall back to stage-level user (phase_index = null)
        $userId = $productStage->getUserForLocation($branchId, $cityId, $stateId);

        return $userId;
    }

    // ── Query Methods (from Stage A) ──

    public function getOrderedStages(): Collection
    {
        return Stage::enabled()->mainStages()->get();
    }

    public function getStageByKey(string $key): ?Stage
    {
        return Stage::where('stage_key', $key)->first();
    }

    public function getSubStages(string $parentKey): Collection
    {
        return Stage::subStagesOf($parentKey)->get();
    }

    public function isParallelStage(string $key): bool
    {
        $stage = $this->getStageByKey($key);

        return $stage && ($stage->is_parallel || $stage->parent_stage_key !== null);
    }

    public function getMainStageKeys(): array
    {
        return Stage::enabled()->mainStages()->pluck('stage_key')->toArray();
    }

    // ── Initialization ──

    /**
     * Create stage assignments + loan progress for a new loan.
     * Uses only the base 14 stages (optional stages added via product_stages in Stage I).
     */
    public function initializeStages(LoanDetail $loan): void
    {
        $baseStageKeys = [
            'inquiry', 'document_selection', 'document_collection',
            'parallel_processing', 'app_number', 'bsm_osv', 'legal_verification', 'technical_valuation', 'sanction_decision',
            'rate_pf', 'sanction', 'docket', 'kfs', 'esign', 'disbursement', 'otc_clearance',
        ];

        $stages = Stage::whereIn('stage_key', $baseStageKeys)->where('is_enabled', true)->get();
        $mainCount = 0;

        foreach ($stages as $stage) {
            $isParallel = $stage->parent_stage_key !== null;

            StageAssignment::create([
                'loan_id' => $loan->id,
                'stage_key' => $stage->stage_key,
                'status' => 'pending',
                'priority' => 'normal',
                'is_parallel_stage' => $isParallel,
                'parent_stage_key' => $stage->parent_stage_key,
            ]);

            if (! $isParallel && $stage->parent_stage_key === null) {
                $mainCount++;
            }
        }

        LoanProgress::create([
            'loan_id' => $loan->id,
            'total_stages' => $mainCount,
            'completed_stages' => 0,
            'overall_percentage' => 0,
        ]);
    }

    /**
     * Auto-complete specific stages (used when converting from quotation).
     */
    public function autoCompleteStages(LoanDetail $loan, array $stageKeys): void
    {
        foreach ($stageKeys as $key) {
            $assignment = $loan->stageAssignments()->where('stage_key', $key)->first();
            if ($assignment) {
                $assignment->update([
                    'status' => 'completed',
                    'started_at' => now(),
                    'completed_at' => now(),
                    'completed_by' => auth()->id(),
                ]);
            }
        }

        $this->recalculateProgress($loan);
    }

    // ── Stage Transitions ──

    /**
     * Update a stage's status with validation and auto-advancement.
     */
    public function updateStageStatus(LoanDetail $loan, string $stageKey, string $newStatus, ?int $userId = null): StageAssignment
    {
        $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->firstOrFail();

        if ($assignment->status === $newStatus) {
            return $assignment;
        }

        if (! $assignment->canTransitionTo($newStatus)) {
            throw new \RuntimeException(
                "Cannot transition stage '{$stageKey}' from '{$assignment->status}' to '{$newStatus}'"
            );
        }

        // Block completion if pending queries
        if ($newStatus === 'completed' && $assignment->hasPendingQueries()) {
            throw new \RuntimeException(
                "Cannot complete stage '{$stageKey}' — there are unresolved queries."
            );
        }

        $oldStatus = $assignment->status;
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'in_progress' && ! $assignment->started_at) {
            $updateData['started_at'] = now();
        }

        if (in_array($newStatus, ['completed', 'rejected', 'skipped'])) {
            $updateData['completed_at'] = now();
            $updateData['completed_by'] = $userId ?? auth()->id();
        }

        $assignment->update($updateData);

        ActivityLog::log('update_stage_status', $assignment, [
            'loan_number' => $loan->loan_number,
            'stage_key' => $stageKey,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        if (in_array($newStatus, ['completed', 'skipped'])) {
            $this->handleStageCompletion($loan, $stageKey);
        }

        $this->recalculateProgress($loan);
        $loan->touch();

        return $assignment->fresh();
    }

    /**
     * Soft-revert a completed stage if its data is no longer complete.
     * Preserves all data (notes, assigned_to, timestamps) — only changes status.
     *
     * @param  bool  $isStillComplete  Whether the stage's completion criteria is still met
     * @return bool Whether a revert occurred
     */
    public function revertStageIfIncomplete(LoanDetail $loan, string $stageKey, bool $isStillComplete): bool
    {
        if ($isStillComplete) {
            return false;
        }

        $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->first();
        if (! $assignment || $assignment->status !== 'completed') {
            return false;
        }

        // Revert this stage: completed → in_progress
        $assignment->update([
            'previous_status' => 'completed',
            'status' => 'in_progress',
        ]);

        // Find and revert the next stage(s)
        $nextKey = $this->getNextStage($stageKey);
        while ($nextKey && ! $loan->stageAssignments()->where('stage_key', $nextKey)->exists()) {
            $nextKey = $this->getNextStage($nextKey);
        }

        if ($nextKey) {
            $nextAssignment = $loan->stageAssignments()->where('stage_key', $nextKey)->first();
            if ($nextAssignment && in_array($nextAssignment->status, ['in_progress', 'pending'])) {
                $nextAssignment->update([
                    'previous_status' => $nextAssignment->status,
                    'status' => 'pending',
                ]);

                // For parallel processing: also revert sub-stages to pending
                if ($nextKey === 'parallel_processing') {
                    $loan->stageAssignments()
                        ->where('parent_stage_key', 'parallel_processing')
                        ->whereIn('status', ['in_progress', 'pending'])
                        ->get()
                        ->each(function ($sub) {
                            $sub->update([
                                'previous_status' => $sub->status,
                                'status' => 'pending',
                            ]);
                        });
                }
            }

            // Update loan's current stage back
            $loan->update(['current_stage' => $stageKey]);
        }

        ActivityLog::log('stage_reverted', $assignment, [
            'loan_number' => $loan->loan_number,
            'stage_key' => $stageKey,
            'reason' => 'Stage data no longer meets completion criteria after edit',
        ]);

        $this->recalculateProgress($loan);
        $loan->touch();

        return true;
    }

    /**
     * Handle post-completion logic: auto-advance to next stage.
     */
    protected function handleStageCompletion(LoanDetail $loan, string $completedStageKey): void
    {
        $assignment = $loan->stageAssignments()->where('stage_key', $completedStageKey)->first();

        // Parallel sub-stage → sequential unlock: 4a → 4b → [4c, 4d, 4e in parallel]
        if ($assignment && ($assignment->is_parallel_stage || $assignment->parent_stage_key !== null)) {
            // When app_number completes, start ONLY bsm_osv
            if ($completedStageKey === 'app_number') {
                $this->startSingleParallelSubStage($loan, 'bsm_osv');
            }

            // When bsm_osv completes, start all remaining parallel sub-stages
            if ($completedStageKey === 'bsm_osv') {
                $this->startRemainingParallelSubStages($loan);
            }

            $this->checkParallelCompletion($loan);

            return;
        }

        // Fund transfer: skip OTC stage and complete loan (before sequential advancement)
        if ($completedStageKey === 'disbursement') {
            $disbursement = $loan->disbursement;
            if ($disbursement && $disbursement->disbursement_type === 'fund_transfer') {
                $otcAssignment = $loan->stageAssignments()->where('stage_key', 'otc_clearance')->first();
                if ($otcAssignment && $otcAssignment->status !== 'completed') {
                    $otcAssignment->update(['status' => 'skipped', 'completed_at' => now(), 'completed_by' => auth()->id()]);
                }
                $loan->update([
                    'status' => LoanDetail::STATUS_COMPLETED,
                    'current_stage' => 'disbursement',
                ]);

                return;
            }
            // Cheque: falls through to normal sequential advancement → otc_clearance
        }

        // Sequential → advance to next and auto-start (skip stages not in this loan)
        $nextKey = $this->getNextStage($completedStageKey);
        while ($nextKey && ! $loan->stageAssignments()->where('stage_key', $nextKey)->exists()) {
            $nextKey = $this->getNextStage($nextKey);
        }
        if ($nextKey) {
            $loan->update(['current_stage' => $nextKey]);

            $this->assignNextStage($loan, $nextKey);

            // If entering parallel processing, auto-start parent + sub-stages
            if ($nextKey === 'parallel_processing') {
                $parentAssignment = $loan->stageAssignments()->where('stage_key', 'parallel_processing')->first();
                if ($parentAssignment) {
                    $parentAssignment->update(['status' => 'in_progress', 'started_at' => now()]);
                }
                $this->autoAssignParallelSubStages($loan);
            } else {
                // Auto-start the next stage
                $nextAssignment = $loan->stageAssignments()->where('stage_key', $nextKey)->first();
                if ($nextAssignment && $nextAssignment->status === 'pending') {
                    $nextAssignment->update(['status' => 'in_progress', 'started_at' => now()]);
                }
            }
        }

        // When sanction completes, calculate expected docket date from app_number notes
        if ($completedStageKey === 'sanction') {
            $appNumberAssignment = $loan->stageAssignments()->where('stage_key', 'app_number')->first();
            if ($appNumberAssignment) {
                $appNotes = $appNumberAssignment->getNotesData();
                $expectedDate = null;

                if (! empty($appNotes['custom_docket_date'])) {
                    $expectedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $appNotes['custom_docket_date'])->toDateString();
                } elseif (! empty($appNotes['docket_days_offset'])) {
                    $expectedDate = now()->addDays((int) $appNotes['docket_days_offset'])->toDateString();
                }

                if ($expectedDate) {
                    $loan->update(['expected_docket_date' => $expectedDate]);
                }
            }
        }

        // OTC clearance completes the loan
        if ($completedStageKey === 'otc_clearance') {
            $loan->update(['status' => LoanDetail::STATUS_COMPLETED]);
            app(NotificationService::class)->notifyLoanCompleted($loan);
        }
    }

    /**
     * Get the next main stage key after the given one.
     */
    public function getNextStage(string $currentStageKey): ?string
    {
        $mainKeys = $this->getMainStageKeys();
        $currentIndex = array_search($currentStageKey, $mainKeys);

        if ($currentIndex === false || $currentIndex >= count($mainKeys) - 1) {
            return null;
        }

        return $mainKeys[$currentIndex + 1];
    }

    /**
     * Can the given stage be started?
     */
    public function canStartStage(LoanDetail $loan, string $stageKey): bool
    {
        $stage = $this->getStageByKey($stageKey);
        if (! $stage) {
            return false;
        }

        // Sub-stages can start when parent is current or in_progress
        if ($stage->parent_stage_key) {
            $parentAssignment = $loan->getStageAssignment($stage->parent_stage_key);

            return $parentAssignment && in_array($parentAssignment->status, ['in_progress']);
        }

        $mainKeys = $this->getMainStageKeys();
        $index = array_search($stageKey, $mainKeys);
        if ($index === 0) {
            return true;
        }

        // Rate & PF: requires is_sanctioned = true (from sanction_decision stage)
        if ($stageKey === 'rate_pf') {
            if (! $loan->is_sanctioned) {
                return false;
            }

            $anySubCompleted = $loan->stageAssignments()
                ->where('parent_stage_key', 'parallel_processing')
                ->where('status', 'completed')
                ->exists();

            return $anySubCompleted;
        }

        // Find the actual previous stage that exists in this loan's assignments
        $prevKey = null;
        for ($i = $index - 1; $i >= 0; $i--) {
            $candidate = $mainKeys[$i];
            if ($loan->getStageAssignment($candidate)) {
                $prevKey = $candidate;
                break;
            }
        }

        if (! $prevKey) {
            return false;
        }

        $prevAssignment = $loan->getStageAssignment($prevKey);
        $prevCompleted = $prevAssignment && in_array($prevAssignment->status, ['completed', 'skipped']);

        if (! $prevCompleted) {
            return false;
        }

        // If previous stage is rate_pf or parallel_processing, also require parallel_processing to be fully completed
        if (in_array($prevKey, ['rate_pf', 'parallel_processing'])) {
            $parallelAssignment = $loan->getStageAssignment('parallel_processing');

            return $parallelAssignment && in_array($parallelAssignment->status, ['completed', 'skipped']);
        }

        return true;
    }

    // ── Assignment ──

    public function assignStage(LoanDetail $loan, string $stageKey, int $userId): StageAssignment
    {
        $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->firstOrFail();
        $assignment->update(['assigned_to' => $userId]);

        ActivityLog::log('assign_stage', $assignment, [
            'loan_number' => $loan->loan_number,
            'stage_key' => $stageKey,
            'assigned_to_name' => User::find($userId)?->name,
        ]);

        return $assignment->fresh();
    }

    public function skipStage(LoanDetail $loan, string $stageKey, ?int $userId = null): StageAssignment
    {
        return $this->updateStageStatus($loan, $stageKey, 'skipped', $userId);
    }

    /**
     * Auto-assign a stage to the best-fit user.
     */
    public function autoAssignStage(LoanDetail $loan, string $stageKey): ?StageAssignment
    {
        // Parallel processing parent is just a label — never assign
        if ($stageKey === 'parallel_processing') {
            return $loan->stageAssignments()->where('stage_key', $stageKey)->first();
        }

        $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->first();
        if (! $assignment || $assignment->assigned_to) {
            return $assignment;
        }

        $userId = $this->findBestAssignee($stageKey, $loan->branch_id, $loan->bank_id, $loan->product_id, $loan->created_by, $loan->assigned_advisor);
        if (! $userId) {
            return $assignment;
        }

        $updateData = ['assigned_to' => $userId];
        // Auto-start the stage if it's still pending
        if ($assignment->status === 'pending') {
            $updateData['status'] = 'in_progress';
            $updateData['started_at'] = now();
        }
        $assignment->update($updateData);

        StageTransfer::create([
            'stage_assignment_id' => $assignment->id,
            'loan_id' => $loan->id,
            'stage_key' => $stageKey,
            'transferred_from' => auth()->id() ?? $loan->created_by,
            'transferred_to' => $userId,
            'reason' => 'Auto-assigned on stage advance',
            'transfer_type' => 'auto',
        ]);

        ActivityLog::log('auto_assign_stage', $assignment, [
            'loan_number' => $loan->loan_number,
            'stage_key' => $stageKey,
            'assigned_to_name' => User::find($userId)?->name,
        ]);

        return $assignment->fresh();
    }

    /**
     * Auto-assign parallel sub-stages. Only starts app_number first;
     * other sub-stages wait until app_number completes.
     */
    public function autoAssignParallelSubStages(LoanDetail $loan): void
    {
        $subStages = $loan->stageAssignments()
            ->where('parent_stage_key', 'parallel_processing')
            ->whereNull('assigned_to')
            ->get();

        foreach ($subStages as $assignment) {
            // Only auto-start app_number; others wait until app_number completes
            if ($assignment->stage_key !== 'app_number') {
                continue;
            }

            $userId = $this->findBestAssignee($assignment->stage_key, $loan->branch_id, $loan->bank_id, $loan->product_id, $loan->created_by, $loan->assigned_advisor);
            $updateData = ['status' => 'in_progress', 'started_at' => now()];
            if ($userId) {
                $updateData['assigned_to'] = $userId;
            }
            $assignment->update($updateData);
        }
    }

    /**
     * Start a single parallel sub-stage by key.
     */
    private function startSingleParallelSubStage(LoanDetail $loan, string $stageKey): void
    {
        $assignment = $loan->stageAssignments()
            ->where('stage_key', $stageKey)
            ->where('status', 'pending')
            ->first();

        if (! $assignment) {
            return;
        }

        $userId = $this->findBestAssignee($stageKey, $loan->branch_id, $loan->bank_id, $loan->product_id, $loan->created_by, $loan->assigned_advisor);
        $updateData = ['status' => 'in_progress', 'started_at' => now()];
        if ($userId) {
            $updateData['assigned_to'] = $userId;
        }
        $assignment->update($updateData);
    }

    /**
     * Start remaining parallel sub-stages after bsm_osv completes (4c, 4d, 4e).
     * Uses workflow config snapshot to determine roles and assignment.
     */
    private function startRemainingParallelSubStages(LoanDetail $loan): void
    {
        $pendingSubs = $loan->stageAssignments()
            ->where('parent_stage_key', 'parallel_processing')
            ->where('status', 'pending')
            ->get();

        foreach ($pendingSubs as $assignment) {
            $role = $this->getLoanStageRole($loan, $assignment->stage_key);
            $userId = $this->findUserForRole($role, $loan, $assignment->stage_key);

            $updateData = ['status' => 'in_progress', 'started_at' => now()];
            if ($userId) {
                $updateData['assigned_to'] = $userId;
            }
            $assignment->update($updateData);

            if ($userId) {
                StageTransfer::create([
                    'stage_assignment_id' => $assignment->id,
                    'loan_id' => $loan->id,
                    'stage_key' => $assignment->stage_key,
                    'transferred_from' => auth()->id() ?? $loan->created_by,
                    'transferred_to' => $userId,
                    'reason' => 'Auto-assigned to '.str_replace('_', ' ', $role),
                    'transfer_type' => 'auto',
                ]);
            }
        }
    }

    /**
     * Find the best office employee for a loan.
     * Priority: product stage config → branch default OE → any OE in branch → any active OE.
     */
    private function findOfficeEmployeeForLoan(LoanDetail $loan, string $stageKey = 'disbursement'): ?int
    {
        // Priority -1: Product stage config (bank-aware via product)
        if ($loan->product_id) {
            $stage = $this->getStageByKey($stageKey);
            if ($stage) {
                $productStage = ProductStage::where('product_id', $loan->product_id)
                    ->where('stage_id', $stage->id)->first();
                if ($productStage) {
                    $branch = $loan->branch_id ? Branch::with('location.parent')->find($loan->branch_id) : null;
                    $cityId = $branch?->location_id;
                    $stateId = $branch?->location?->parent_id;
                    $userId = $productStage->getUserForLocation($loan->branch_id, $cityId, $stateId);
                    if ($userId) {
                        $user = User::where('id', $userId)->where('is_active', true)->first();
                        if ($user) {
                            return $user->id;
                        }
                    }
                }
            }
        }

        // Priority 0b: Default office employee for the loan's branch
        if ($loan->branch_id) {
            $defaultOE = User::whereHas('branches', fn ($q) => $q->where('branches.id', $loan->branch_id)->where('user_branches.is_default_office_employee', true))
                ->where('is_active', true)
                ->first();
            if ($defaultOE) {
                return $defaultOE->id;
            }
        }

        // Fallback: any active office employee in the same branch
        if ($loan->branch_id) {
            $branchOE = User::whereHas('roles', fn ($q) => $q->where('slug', 'office_employee'))
                ->whereHas('branches', fn ($q) => $q->where('branches.id', $loan->branch_id))
                ->where('is_active', true)
                ->first();
            if ($branchOE) {
                return $branchOE->id;
            }
        }

        // Last resort: any active office employee
        $anyOE = User::whereHas('roles', fn ($q) => $q->where('slug', 'office_employee'))
            ->where('is_active', true)
            ->first();

        return $anyOE?->id;
    }

    /**
     * Find the best user for a stage.
     * Priority: product stage specific user → bank default employee → role+bank+branch match
     */
    public function findBestAssignee(string $stageKey, ?int $branchId, ?int $bankId, ?int $productId = null, ?int $loanCreatorId = null, ?int $advisorId = null): ?int
    {
        // Priority -1: Product stage has a location/branch-specific or default user assigned
        if ($productId) {
            $stage = $this->getStageByKey($stageKey);
            if ($stage) {
                $productStage = ProductStage::where('product_id', $productId)
                    ->where('stage_id', $stage->id)
                    ->first();
                if ($productStage) {
                    // Resolve location from branch
                    $branch = $branchId ? \App\Models\Branch::with('location.parent')->find($branchId) : null;
                    $cityId = $branch?->location_id;
                    $stateId = $branch?->location?->parent_id;

                    // Check branch → city → state → product default
                    $assignedUserId = $productStage->getUserForLocation($branchId, $cityId, $stateId);
                    if ($assignedUserId) {
                        $user = User::where('id', $assignedUserId)->where('is_active', true)->first();
                        if ($user) {
                            return $user->id;
                        }
                    }
                }
            }
        }

        $eligibleRoles = self::getStageRoleEligibility($stageKey);
        if (empty($eligibleRoles)) {
            return null;
        }

        // Priority 0a: Assigned advisor (for advisor-eligible stages)
        $advisorRoles = ['loan_advisor', 'branch_manager', 'bdh'];
        if ($advisorId && ! empty(array_intersect($eligibleRoles, $advisorRoles))) {
            $advisor = User::where('id', $advisorId)->where('is_active', true)->first();
            if ($advisor && $advisor->hasAnyRole($eligibleRoles)) {
                return $advisor->id;
            }
        }

        $baseQuery = User::where('is_active', true)->whereHas('roles', fn ($q) => $q->whereIn('slug', $eligibleRoles));
        $hasBankRole = in_array('bank_employee', $eligibleRoles);

        if ($hasBankRole && $bankId) {
            // Priority 0: Bank's default employee for this city
            $cityId = $branchId ? \App\Models\Branch::find($branchId)?->location_id : null;
            $bank = Bank::find($bankId);
            if ($bank) {
                $defaultBEId = $bank->getDefaultEmployeeForCity($cityId);
                if ($defaultBEId) {
                    $defaultEmp = User::where('id', $defaultBEId)->where('is_active', true)->first();
                    if ($defaultEmp) {
                        return $defaultEmp->id;
                    }
                }
            }

            // Priority 1: Bank employee from pivot table + in branch
            if ($branchId) {
                $user = User::where('is_active', true)
                    ->whereHas('employerBanks', fn ($q) => $q->where('banks.id', $bankId))
                    ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))->first();
                if ($user) {
                    return $user->id;
                }
            }

            // Priority 2: Bank employee from pivot table (any branch)
            $user = User::where('is_active', true)
                ->whereHas('employerBanks', fn ($q) => $q->where('banks.id', $bankId))->first();
            if ($user) {
                return $user->id;
            }

            // Other eligible roles in branch
            $otherRoles = array_diff($eligibleRoles, ['bank_employee']);
            if ($branchId && ! empty($otherRoles)) {
                $user = User::where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->whereIn('slug', $otherRoles))
                    ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))->first();
                if ($user) {
                    return $user->id;
                }
            }
        }

        // Priority 0b: Default office employee for the branch (from user_branches pivot)
        if (in_array('office_employee', $eligibleRoles) && $branchId) {
            $defaultOE = User::where('is_active', true)
                ->whereHas('roles', fn ($q) => $q->where('slug', 'office_employee'))
                ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId)->where('user_branches.is_default_office_employee', true))
                ->first();
            if ($defaultOE) {
                return $defaultOE->id;
            }
        }

        // Priority for loan_advisor/branch_manager: default to loan creator
        if ($loanCreatorId && (in_array('loan_advisor', $eligibleRoles) || in_array('branch_manager', $eligibleRoles) || in_array('bdh', $eligibleRoles))) {
            $creator = User::where('id', $loanCreatorId)->where('is_active', true)->first();
            if ($creator && $creator->hasAnyRole($eligibleRoles)) {
                return $creator->id;
            }
        }

        // Branch-based match
        if ($branchId) {
            $user = (clone $baseQuery)->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))->first();
            if ($user) {
                return $user->id;
            }
        }

        // Any matching role
        return $baseQuery->first()?->id;
    }

    // ── Transfer ──

    /**
     * Transfer a stage to another user with history tracking.
     */
    public function transferStage(LoanDetail $loan, string $stageKey, int $toUserId, ?string $reason = null): StageAssignment
    {
        $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->firstOrFail();
        $fromUserId = auth()->id();

        $assignment->update(['assigned_to' => $toUserId]);

        StageTransfer::create([
            'stage_assignment_id' => $assignment->id,
            'loan_id' => $loan->id,
            'stage_key' => $stageKey,
            'transferred_from' => $fromUserId,
            'transferred_to' => $toUserId,
            'reason' => $reason,
            'transfer_type' => 'manual',
        ]);

        // Reassign open queries on this stage to the new user
        StageQuery::where('loan_id', $loan->id)
            ->where('stage_key', $stageKey)
            ->whereIn('status', ['pending', 'responded'])
            ->update(['stage_assignment_id' => $assignment->id]);

        ActivityLog::log('transfer_stage', $assignment, [
            'loan_number' => $loan->loan_number,
            'stage_key' => $stageKey,
            'from_user' => User::find($fromUserId)?->name,
            'to_user' => User::find($toUserId)?->name,
            'reason' => $reason,
        ]);

        $loan->touch();

        return $assignment->fresh();
    }

    // ── Rejection ──

    /**
     * Reject the entire loan from a stage.
     */
    public function rejectLoan(LoanDetail $loan, string $stageKey, string $reason, ?int $userId = null): LoanDetail
    {
        $userId = $userId ?? auth()->id();

        $loan->update([
            'status' => LoanDetail::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejected_by' => $userId,
            'rejected_stage' => $stageKey,
            'rejection_reason' => $reason,
        ]);

        $assignment = $loan->stageAssignments()->where('stage_key', $stageKey)->first();
        if ($assignment) {
            $assignment->update([
                'previous_status' => $assignment->status,
                'status' => 'rejected',
                'completed_at' => now(),
                'completed_by' => $userId,
            ]);
        }

        ActivityLog::log('reject_loan', $loan, [
            'loan_number' => $loan->loan_number,
            'rejected_stage' => $stageKey,
            'reason' => $reason,
        ]);

        return $loan->fresh();
    }

    // ── Parallel Processing ──

    /**
     * Assign the next stage to the correct user based on workflow config snapshot.
     * Uses getLoanStageRole() to determine role, then findUserForRole() to find the user.
     */
    protected function assignNextStage(LoanDetail $loan, string $nextKey): void
    {
        $nextAssignment = $loan->stageAssignments()->where('stage_key', $nextKey)->first();
        if (! $nextAssignment || $nextAssignment->assigned_to) {
            return;
        }

        $role = $this->getLoanStageRole($loan, $nextKey);
        $userId = $this->findUserForRole($role, $loan, $nextKey);

        if ($userId) {
            $nextAssignment->update(['assigned_to' => $userId]);

            StageTransfer::create([
                'stage_assignment_id' => $nextAssignment->id,
                'loan_id' => $loan->id,
                'stage_key' => $nextKey,
                'transferred_from' => auth()->id() ?? $loan->created_by,
                'transferred_to' => $userId,
                'reason' => 'Auto-assigned to '.str_replace('_', ' ', $role),
                'transfer_type' => 'auto',
            ]);
        }
    }

    /**
     * Check if all parallel sub-stages are complete.
     */
    public function checkParallelCompletion(LoanDetail $loan): bool
    {
        $subStages = $loan->stageAssignments()->subStagesOf('parallel_processing')->get();

        $allDone = $subStages->every(fn ($sa) => in_array($sa->status, ['completed', 'skipped']));

        if ($allDone) {
            $parent = $loan->stageAssignments()->where('stage_key', 'parallel_processing')->first();
            if ($parent && $parent->status !== 'completed') {
                $parent->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => auth()->id(),
                ]);

                $nextKey = $this->getNextStage('parallel_processing');
                while ($nextKey && ! $loan->stageAssignments()->where('stage_key', $nextKey)->exists()) {
                    $nextKey = $this->getNextStage($nextKey);
                }
                if ($nextKey) {
                    $loan->update(['current_stage' => $nextKey]);
                    $this->assignNextStage($loan, $nextKey);

                    // Auto-start the next stage
                    $nextAssignment = $loan->stageAssignments()->where('stage_key', $nextKey)->first();
                    if ($nextAssignment && $nextAssignment->status === 'pending') {
                        $nextAssignment->update(['status' => 'in_progress', 'started_at' => now()]);
                    }
                }
            }

            $this->recalculateProgress($loan);

            return true;
        }

        return false;
    }

    public function getParallelSubStages(LoanDetail $loan): Collection
    {
        return $loan->stageAssignments()
            ->subStagesOf('parallel_processing')
            ->with(['stage', 'assignee'])
            ->get();
    }

    // ── Progress ──

    /**
     * Recalculate loan progress percentages.
     */
    public function recalculateProgress(LoanDetail $loan): LoanProgress
    {
        $progress = $loan->progress ?? LoanProgress::create([
            'loan_id' => $loan->id,
            'total_stages' => 10,
        ]);

        $mainAssignments = $loan->stageAssignments()->mainStages()->get();
        $total = $mainAssignments->count();
        $completed = $mainAssignments->whereIn('status', ['completed', 'skipped'])->count();
        $percentage = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        $snapshot = $loan->stageAssignments()->get()->map(fn ($sa) => [
            'stage_key' => $sa->stage_key,
            'status' => $sa->status,
            'assigned_to' => $sa->assigned_to,
        ])->toArray();

        $progress->update([
            'total_stages' => $total,
            'completed_stages' => $completed,
            'overall_percentage' => $percentage,
            'workflow_snapshot' => $snapshot,
        ]);

        return $progress->fresh();
    }

    /**
     * Get all stage assignments for a loan, ordered by sequence.
     */
    public function getLoanStageStatus(LoanDetail $loan): Collection
    {
        return $loan->stageAssignments()
            ->with(['stage', 'assignee'])
            ->get()
            ->sortBy(fn ($sa) => ($sa->stage?->sequence_order ?? 999) * 1000 + ($sa->stage?->id ?? 999));
    }
}
