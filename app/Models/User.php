<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasFactory, HasPushSubscriptions, Impersonate, Notifiable;

    /**
     * Get advisor-eligible role slugs (from cached DB query).
     */
    public static function advisorEligibleRoles(): array
    {
        return Role::advisorEligibleSlugs();
    }

    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'created_by', 'phone',
        'employee_id', 'default_branch_id', 'task_bank_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branches')
            ->withPivot('is_default_office_employee');
    }

    public function defaultBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'default_branch_id');
    }

    public function taskBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'task_bank_id');
    }

    public function employerBanks(): BelongsToMany
    {
        return $this->belongsToMany(Bank::class, 'bank_employees')
            ->withPivot('is_default', 'location_id')
            ->withTimestamps();
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_user')->withTimestamps();
    }

    // ── Role Helpers ──

    /**
     * Check if user has a specific role by slug.
     */
    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('slug', $slug);
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles->whereIn('slug', $slugs)->isNotEmpty();
    }

    /**
     * Get all role slugs for this user.
     */
    public function getRoleSlugsAttribute(): array
    {
        return $this->roles->pluck('slug')->toArray();
    }

    // ── Role Convenience Methods ──

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isBankEmployee(): bool
    {
        return $this->hasRole('bank_employee');
    }

    public function isLoanAdvisor(): bool
    {
        return $this->hasRole('loan_advisor');
    }

    public function hasWorkflowRole(): bool
    {
        return $this->hasAnyRole(static::advisorEligibleRoles()) || $this->hasRole('bank_employee') || $this->hasRole('office_employee');
    }

    public function canCreateLoans(): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        return $this->hasAnyRole(static::advisorEligibleRoles());
    }

    // ── Permission ──

    public function hasPermission(string $slug): bool
    {
        return app(\App\Services\PermissionService::class)->userHasPermission($this, $slug);
    }

    // ── Display Helpers ──

    public function getRoleLabelAttribute(): string
    {
        return $this->roles->pluck('name')->implode(', ') ?: '—';
    }

    public function getWorkflowRoleLabelAttribute(): string
    {
        $workflowRoles = $this->roles->whereNotIn('slug', ['super_admin', 'admin']);

        return $workflowRoles->first()?->name ?? '';
    }

    public function getWorkflowRoleLabelGuAttribute(): string
    {
        $labels = Role::gujaratiLabels();
        $workflowRoles = $this->roles->whereNotIn('slug', ['super_admin', 'admin']);

        return $workflowRoles->map(fn ($r) => $labels[$r->slug] ?? $r->name)->implode(', ');
    }

    // ── Scopes ──

    public function scopeAdvisorEligible($query)
    {
        return $query->whereHas('roles', fn ($q) => $q->whereIn('slug', static::advisorEligibleRoles()))
            ->where('is_active', true);
    }

    // ── Impersonation ──

    public function canImpersonate(): bool
    {
        if (config('app.allow_impersonate_all')) {
            return true;
        }

        return $this->isSuperAdmin();
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->isSuperAdmin();
    }
}
