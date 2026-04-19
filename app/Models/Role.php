<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'can_be_advisor', 'is_system'];

    protected function casts(): array
    {
        return [
            'can_be_advisor' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        $invalidate = function (): void {
            app(\App\Services\PermissionService::class)->clearAllCaches();
            static::clearAdvisorCache();
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    // ── Scopes ──

    public function scopeAdvisorEligible($query)
    {
        return $query->where('can_be_advisor', true);
    }

    public function scopeWorkflow($query)
    {
        return $query->where('is_system', false);
    }

    // ── Cached Queries ──

    /**
     * Get advisor-eligible role slugs (cached 5 min).
     */
    public static function advisorEligibleSlugs(): array
    {
        return Cache::remember('advisor_eligible_roles', 300, function () {
            return static::where('can_be_advisor', true)->pluck('slug')->toArray();
        });
    }

    /**
     * Clear the advisor-eligible cache.
     */
    public static function clearAdvisorCache(): void
    {
        Cache::forget('advisor_eligible_roles');
    }

    /**
     * Role labels in Gujarati (for bilingual display).
     */
    public static function gujaratiLabels(): array
    {
        return [
            'super_admin' => 'સુપર એડમિન',
            'admin' => 'એડમિન',
            'branch_manager' => 'બ્રાન્ચ મેનેજર',
            'bdh' => 'બિઝનેસ ડેવલપમેન્ટ હેડ',
            'loan_advisor' => 'લોન સલાહકાર',
            'bank_employee' => 'બેંક કર્મચારી',
            'office_employee' => 'ઓફિસ કર્મચારી',
        ];
    }
}
