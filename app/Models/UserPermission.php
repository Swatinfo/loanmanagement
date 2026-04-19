<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends Model
{
    protected $fillable = ['user_id', 'permission_id', 'type'];

    protected static function booted(): void
    {
        $invalidate = function (UserPermission $record): void {
            if ($user = $record->user) {
                app(\App\Services\PermissionService::class)->clearUserCache($user);
            }
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
