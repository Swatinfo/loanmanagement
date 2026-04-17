<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $fillable = ['name', 'code', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bank_employees')
            ->withPivot('is_default', 'location_id')
            ->withTimestamps();
    }

    /**
     * Get the default employee for a specific city (location_id).
     * Falls back to global default (location_id IS NULL) if no city-level default.
     */
    public function getDefaultEmployeeForCity(?int $cityId): ?int
    {
        if ($cityId) {
            $cityDefault = \DB::table('bank_employees')
                ->where('bank_id', $this->id)
                ->where('is_default', true)
                ->where('location_id', $cityId)
                ->value('user_id');
            if ($cityDefault) {
                return $cityDefault;
            }
        }

        // Fallback: global default (no location)
        return \DB::table('bank_employees')
            ->where('bank_id', $this->id)
            ->where('is_default', true)
            ->whereNull('location_id')
            ->value('user_id');
    }

    public function stageConfigs(): HasMany
    {
        return $this->hasMany(BankStageConfig::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'bank_location')->withTimestamps();
    }

    // Scopes

    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }
}
