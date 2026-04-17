<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductStage extends Model
{
    use HasAuditColumns;

    protected $fillable = [
        'product_id', 'stage_id', 'is_enabled', 'default_assignee_role',
        'default_user_id', 'auto_skip', 'allow_skip', 'sort_order', 'sub_actions_override',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'auto_skip' => 'boolean',
            'allow_skip' => 'boolean',
            'sort_order' => 'integer',
            'sub_actions_override' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function defaultUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_user_id');
    }

    public function branchUsers(): HasMany
    {
        return $this->hasMany(ProductStageUser::class);
    }

    /**
     * Get the assigned user for a specific branch.
     */
    public function getUserForBranch(?int $branchId): ?int
    {
        if (! $branchId) {
            return $this->default_user_id;
        }

        $branchAssignment = $this->branchUsers()->where('branch_id', $branchId)->first();

        return $branchAssignment?->user_id ?? $this->default_user_id;
    }

    /**
     * Get the best assigned user considering location hierarchy.
     * Priority: branch → city → state → product default
     * When phaseIndex is provided, looks for phase-specific assignments first, then falls back to stage-level.
     */
    public function getUserForLocation(?int $branchId, ?int $cityId, ?int $stateId, ?int $phaseIndex = null): ?int
    {
        // If phase-specific, try phase assignments first
        if ($phaseIndex !== null) {
            $phaseUser = $this->findUserByLocationHierarchy($branchId, $cityId, $stateId, $phaseIndex);
            if ($phaseUser) {
                return $phaseUser;
            }
        }

        // Stage-level (phase_index IS NULL)
        return $this->findUserByLocationHierarchy($branchId, $cityId, $stateId, null) ?? $this->default_user_id;
    }

    /**
     * Search branch users by location hierarchy for a specific phase_index.
     */
    private function findUserByLocationHierarchy(?int $branchId, ?int $cityId, ?int $stateId, ?int $phaseIndex): ?int
    {
        $query = $this->branchUsers();
        if ($phaseIndex !== null) {
            $query = $query->where('phase_index', $phaseIndex);
        } else {
            $query = $query->whereNull('phase_index');
        }

        // 1. Branch-specific
        if ($branchId) {
            $match = (clone $query)->where('branch_id', $branchId)->where('is_default', true)->first();
            if ($match) {
                return $match->user_id;
            }
        }

        // 2. City-level
        if ($cityId) {
            $match = (clone $query)->whereNull('branch_id')->where('location_id', $cityId)->where('is_default', true)->first();
            if ($match) {
                return $match->user_id;
            }
        }

        // 3. State-level
        if ($stateId) {
            $match = (clone $query)->whereNull('branch_id')->where('location_id', $stateId)->where('is_default', true)->first();
            if ($match) {
                return $match->user_id;
            }
        }

        return null;
    }
}
