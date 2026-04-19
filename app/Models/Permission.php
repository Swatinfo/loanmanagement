<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'group', 'description'];

    protected static function booted(): void
    {
        $invalidate = function (): void {
            app(\App\Services\PermissionService::class)->clearAllCaches();
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }
}
