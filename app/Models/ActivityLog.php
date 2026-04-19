<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Extends Spatie's Activity model so the `activity_log` table is used under
 * the hood while the existing `ActivityLog::log()` call sites keep working.
 *
 * Custom columns `ip_address` and `user_agent` are preserved in the new table
 * alongside Spatie's standard columns (causer_*, subject_*, properties, etc.).
 */
class ActivityLog extends SpatieActivity
{
    protected $fillable = [
        'log_name', 'description', 'subject_type', 'subject_id',
        'event', 'causer_type', 'causer_id', 'attribute_changes',
        'properties', 'batch_uuid', 'ip_address', 'user_agent',
    ];

    /**
     * Backward-compatible `user` relation — causer is always a User in this app.
     * Keeps `->with('user')` and `$log->user->name` working across existing views.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * `$log->action` → `description` (legacy column name kept as an accessor).
     */
    public function getActionAttribute(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    /**
     * `$log->user_id` → `causer_id` (legacy column name kept as an accessor).
     */
    public function getUserIdAttribute(): ?int
    {
        return $this->attributes['causer_id'] ?? null;
    }

    /**
     * Log an activity.
     *
     * Shim for the legacy signature. Internally this writes to the new
     * `activity_log` table via Spatie's API and captures impersonator_id in
     * properties when the session is impersonated.
     */
    public static function log(string $action, ?Model $subject = null, ?array $properties = null): self
    {
        $user = auth()->user();
        $properties = $properties ?? [];

        if ($user && method_exists($user, 'isImpersonated') && $user->isImpersonated()) {
            $properties['impersonator_id'] = session('impersonated_by');
        }

        /** @var self $log */
        $log = static::create([
            'log_name' => 'default',
            'description' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'causer_type' => $user ? $user::class : null,
            'causer_id' => $user?->getAuthIdentifier(),
            'properties' => $properties,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        return $log;
    }
}
