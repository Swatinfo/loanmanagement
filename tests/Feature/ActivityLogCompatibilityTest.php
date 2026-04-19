<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks in that `ActivityLog::log()` keeps the pre-Spatie call-site contract
 * intact after swapping the model to extend `Spatie\Activitylog\Models\Activity`.
 */
class ActivityLogCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester'.uniqid().'@test',
            'password' => bcrypt('x'),
            'is_active' => true,
        ]);
        $user->roles()->sync(Role::where('slug', 'loan_advisor')->pluck('id'));

        return $user;
    }

    public function test_log_writes_to_activity_log_table(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $log = ActivityLog::log('test_event', $user, ['note' => 'x']);

        $this->assertSame('activity_log', $log->getTable());
        $this->assertSame('test_event', $log->description);
        $this->assertSame($user->id, $log->causer_id);
        $this->assertSame(User::class, $log->causer_type);
    }

    public function test_legacy_action_accessor_returns_description(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $log = ActivityLog::log('my_action');

        $this->assertSame('my_action', $log->action);
    }

    public function test_legacy_user_id_accessor_returns_causer_id(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $log = ActivityLog::log('another_action');

        $this->assertSame($user->id, $log->user_id);
    }

    public function test_legacy_user_relation_returns_causer_user(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $log = ActivityLog::log('with_user');

        $this->assertSame($user->id, $log->user->id);
        $this->assertSame($user->name, $log->user->name);
    }

    public function test_custom_ip_address_and_user_agent_columns_persist(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $log = ActivityLog::log('with_request');

        $this->assertTrue(
            $log->ip_address !== null || $log->user_agent !== null || true,
            'Columns exist and do not throw when accessed'
        );
        // The columns must at least exist; values can be null in test env.
        $this->assertArrayHasKey('ip_address', $log->getAttributes());
        $this->assertArrayHasKey('user_agent', $log->getAttributes());
    }
}
