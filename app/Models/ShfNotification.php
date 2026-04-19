<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShfNotification extends Model
{
    protected $table = 'shf_notifications';

    const TYPE_INFO = 'info';

    const TYPE_SUCCESS = 'success';

    const TYPE_WARNING = 'warning';

    const TYPE_ERROR = 'error';

    const TYPE_STAGE_UPDATE = 'stage_update';

    const TYPE_ASSIGNMENT = 'assignment';

    protected $fillable = [
        'user_id', 'title', 'message', 'type', 'is_read',
        'loan_id', 'stage_key', 'link',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $notification): void {
            // Live bell via Reverb (in-tab). Reverb runs as a separate process —
            // if it's down (dev machine, restart, misconfigured broadcast driver),
            // the synchronous dispatch throws a ConnectionException. The DB row is
            // already saved at this point, so failing here would bubble a 500 back
            // to the user for an action that actually succeeded. Swallow + log.
            try {
                \App\Events\NotificationBroadcast::dispatch($notification);
            } catch (\Throwable $e) {
                \Log::warning('Notification broadcast failed (Reverb down?)', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Native OS notification via Web Push (out-of-tab / PWA closed).
            // Fires only if the recipient has an active push subscription.
            if ($user = $notification->user) {
                try {
                    $user->notify(new \App\Notifications\ShfPushNotification($notification));
                } catch (\Throwable $e) {
                    \Log::warning('Web push failed', [
                        'notification_id' => $notification->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoanDetail::class, 'loan_id');
    }

    // Scopes

    public function scopeUnread($query): void
    {
        $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $limit = 50): void
    {
        $query->latest()->limit($limit);
    }
}
