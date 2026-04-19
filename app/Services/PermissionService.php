<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    /**
     * Check if a user has a specific permission.
     *
     * Resolution order (3-tier):
     * 1. Super Admin → always true
     * 2. User-specific grant/deny override
     * 3. Any of the user's roles grants the permission
     */
    public function userHasPermission(User $user, string $slug): bool
    {
        // 1. Super Admin bypass
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // 2. Check user-specific override
        $userOverride = $this->getUserOverride($user, $slug);
        if ($userOverride !== null) {
            return $userOverride;
        }

        // 3. Check if ANY of the user's roles has this permission
        return $this->userRolesHavePermission($user, $slug);
    }

    /**
     * Check for user-specific permission override.
     * Returns true (grant), false (deny), or null (no override).
     */
    protected function getUserOverride(User $user, string $slug): ?bool
    {
        $cacheKey = "user_perms:{$user->id}";

        $overrides = Cache::remember($cacheKey, 300, function () use ($user) {
            return UserPermission::where('user_id', $user->id)
                ->join('permissions', 'permissions.id', '=', 'user_permissions.permission_id')
                ->pluck('user_permissions.type', 'permissions.slug')
                ->toArray();
        });

        if (! isset($overrides[$slug])) {
            return null;
        }

        return $overrides[$slug] === 'grant';
    }

    /**
     * Check if any of the user's roles has a given permission.
     */
    public function userRolesHavePermission(User $user, string $slug): bool
    {
        $roleIds = $this->getUserRoleIds($user);
        if (empty($roleIds)) {
            return false;
        }

        $permSlugs = $this->getRolePermissionSlugs($roleIds);

        return in_array($slug, $permSlugs);
    }

    /**
     * Get all role IDs for a user (cached).
     */
    protected function getUserRoleIds(User $user): array
    {
        $cacheKey = "user_role_ids:{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return $user->roles()->pluck('roles.id')->toArray();
        });
    }

    /**
     * Get permission slugs for a set of role IDs (cached).
     */
    protected function getRolePermissionSlugs(array $roleIds): array
    {
        sort($roleIds);
        $cacheKey = 'role_perms:'.implode(',', $roleIds);

        return Cache::remember($cacheKey, 300, function () use ($roleIds) {
            return DB::table('role_permission')
                ->whereIn('role_id', $roleIds)
                ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
                ->pluck('permissions.slug')
                ->unique()
                ->toArray();
        });
    }

    /**
     * Get all permissions for a user (merged roles + overrides).
     */
    public function getUserPermissions(User $user): array
    {
        $allPermissions = Permission::all();
        $result = [];

        foreach ($allPermissions as $permission) {
            $result[$permission->slug] = $this->userHasPermission($user, $permission->slug);
        }

        return $result;
    }

    /**
     * Get all permissions grouped.
     */
    public function getGroupedPermissions(): array
    {
        return Permission::all()->groupBy('group')->toArray();
    }

    /**
     * All known permission slugs (cached 1 hour).
     * Used by Gate::before so @can('slug') / $user->can('slug') resolve here
     * instead of falling through to a missing gate definition.
     * Returns [] quietly if the permissions table is unavailable (e.g., mid-migration).
     */
    public function allSlugs(): array
    {
        return Cache::remember('all_permission_slugs', 3600, function () {
            try {
                return Permission::pluck('slug')->toArray();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /**
     * Clear cached permissions for a user.
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget("user_perms:{$user->id}");
        Cache::forget("user_role_ids:{$user->id}");
    }

    /**
     * Clear cached permissions for roles.
     */
    public function clearRoleCache(): void
    {
        $roleIds = DB::table('roles')->pluck('id')->toArray();
        foreach ($roleIds as $id) {
            Cache::forget("role_perms:{$id}");
        }

        // Combination caches keyed by sorted-id lists — forget every subset we have touched.
        // Cheapest reliable way: also forget by full combos present in role_user.
        DB::table('role_user')
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')
            ->each(function ($userId): void {
                $ids = DB::table('role_user')->where('user_id', $userId)->pluck('role_id')->sort()->values()->toArray();
                if (! empty($ids)) {
                    Cache::forget('role_perms:'.implode(',', $ids));
                }
            });
    }

    /**
     * Clear all permission caches (slugs list + per-role + per-user).
     */
    public function clearAllCaches(): void
    {
        Cache::forget('all_permission_slugs');
        $this->clearRoleCache();

        User::pluck('id')->each(function ($userId): void {
            Cache::forget("user_perms:{$userId}");
            Cache::forget("user_role_ids:{$userId}");
        });
    }
}
