<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    protected $fillable = [
        'stage_key', 'is_enabled', 'stage_name_en', 'stage_name_gu', 'sequence_order',
        'is_parallel', 'parent_stage_key', 'stage_type',
        'description_en', 'description_gu', 'default_role', 'assigned_role', 'sub_actions',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_parallel' => 'boolean',
            'sequence_order' => 'integer',
            'default_role' => 'array',
            'sub_actions' => 'array',
        ];
    }

    // Relationships

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_stage_key', 'stage_key');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_stage_key', 'stage_key');
    }

    // Helpers

    public function isSubStage(): bool
    {
        return $this->parent_stage_key !== null;
    }

    public function isParent(): bool
    {
        return $this->is_parallel && $this->children()->exists();
    }

    public function isDecision(): bool
    {
        return $this->stage_type === 'decision';
    }

    public function bankConfigs(): HasMany
    {
        return $this->hasMany(BankStageConfig::class);
    }

    // Scopes

    public function scopeEnabled($query): void
    {
        $query->where('is_enabled', true);
    }

    public function scopeMainStages($query): void
    {
        $query->whereNull('parent_stage_key')->orderBy('sequence_order');
    }

    public function scopeSubStagesOf($query, string $parentKey): void
    {
        $query->where('parent_stage_key', $parentKey);
    }
}
