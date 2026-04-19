<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PermissionResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles are pre-seeded by the unified_roles_system migration.
        // We only need to ensure the permissions used in these tests exist.
        Permission::firstOrCreate(
            ['slug' => 'view_loans'],
            ['name' => 'View Loans', 'group' => 'Loans']
        );
        Permission::firstOrCreate(
            ['slug' => 'edit_loan'],
            ['name' => 'Edit Loan', 'group' => 'Loans']
        );
    }

    private function makeUser(array $roleSlugs = []): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'u'.uniqid().'@test',
            'password' => bcrypt('secret'),
            'is_active' => true,
        ]);
        $roleIds = Role::whereIn('slug', $roleSlugs)->pluck('id');
        $user->roles()->sync($roleIds);

        return $user->fresh('roles');
    }

    private function grantRole(string $roleSlug, string $permSlug): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $perm = Permission::where('slug', $permSlug)->firstOrFail();
        $role->permissions()->syncWithoutDetaching([$perm->id]);
    }

    /* ── Tier-matrix cases ── */

    public function test_super_admin_bypasses_every_permission(): void
    {
        $user = $this->makeUser(['super_admin']);

        $this->assertTrue($user->hasPermission('view_loans'));
        $this->assertTrue($user->hasPermission('edit_loan'));
        $this->assertTrue($user->hasPermission('nonexistent_slug'));
    }

    public function test_role_grant_alone_grants(): void
    {
        $this->grantRole('loan_advisor', 'view_loans');
        $user = $this->makeUser(['loan_advisor']);

        $this->assertTrue($user->hasPermission('view_loans'));
        $this->assertFalse($user->hasPermission('edit_loan'));
    }

    public function test_user_grant_alone_grants(): void
    {
        $user = $this->makeUser(['bank_employee']);
        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => Permission::where('slug', 'edit_loan')->value('id'),
            'type' => 'grant',
        ]);

        $this->assertTrue($user->hasPermission('edit_loan'));
    }

    public function test_user_deny_overrides_role_grant(): void
    {
        $this->grantRole('loan_advisor', 'view_loans');
        $user = $this->makeUser(['loan_advisor']);

        $this->assertTrue($user->hasPermission('view_loans'));

        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => Permission::where('slug', 'view_loans')->value('id'),
            'type' => 'deny',
        ]);

        $this->assertFalse($user->fresh('roles')->hasPermission('view_loans'));
    }

    public function test_no_role_no_override_denies(): void
    {
        $user = $this->makeUser([]);

        $this->assertFalse($user->hasPermission('view_loans'));
    }

    public function test_multiple_roles_any_grant_wins(): void
    {
        $this->grantRole('bank_employee', 'view_loans');
        $user = $this->makeUser(['loan_advisor', 'bank_employee']);

        $this->assertTrue($user->hasPermission('view_loans'));
    }

    /* ── Gate integration ── */

    public function test_can_method_delegates_to_permission_service(): void
    {
        $this->grantRole('loan_advisor', 'view_loans');
        $user = $this->makeUser(['loan_advisor']);

        $this->assertTrue($user->can('view_loans'));
        $this->assertFalse($user->can('edit_loan'));
    }

    public function test_gate_before_grants_super_admin_every_ability(): void
    {
        $user = $this->makeUser(['super_admin']);

        $this->assertTrue($user->can('view_loans'));
        $this->assertTrue($user->can('edit_loan'));
    }

    /* ── Cache invalidation ── */

    public function test_saving_role_clears_slug_cache(): void
    {
        $service = app(PermissionService::class);
        $service->allSlugs(); // Prime the cache

        $this->assertTrue(Cache::has('all_permission_slugs'));

        Role::create(['name' => 'New Role', 'slug' => 'new_role']);

        $this->assertFalse(Cache::has('all_permission_slugs'));
    }

    public function test_user_permission_write_clears_user_cache(): void
    {
        $user = $this->makeUser(['loan_advisor']);
        $service = app(PermissionService::class);

        // Prime the cache by calling hasPermission once
        $user->hasPermission('view_loans');
        $this->assertTrue(Cache::has("user_perms:{$user->id}"));

        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => Permission::where('slug', 'view_loans')->value('id'),
            'type' => 'grant',
        ]);

        $this->assertFalse(Cache::has("user_perms:{$user->id}"));
    }
}
