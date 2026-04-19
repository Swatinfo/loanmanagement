<?php

namespace Tests\Feature;

use App\Events\NotificationBroadcast;
use App\Models\Role;
use App\Models\ShfNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationBroadcastTest extends TestCase
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

    public function test_creating_notification_dispatches_broadcast_event(): void
    {
        Event::fake([NotificationBroadcast::class]);

        $user = $this->makeUser();

        $notification = ShfNotification::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Hello',
            'type' => 'info',
        ]);

        Event::assertDispatched(NotificationBroadcast::class, function (NotificationBroadcast $e) use ($notification) {
            return $e->notification->id === $notification->id;
        });
    }

    public function test_broadcast_channel_is_private_to_user(): void
    {
        $user = $this->makeUser();
        $notification = ShfNotification::create([
            'user_id' => $user->id,
            'title' => 'x',
            'message' => 'y',
            'type' => 'info',
        ]);

        $event = new NotificationBroadcast($notification);
        $channel = $event->broadcastOn();

        $this->assertSame('private-users.'.$user->id, $channel->name);
    }

    public function test_broadcast_payload_includes_expected_fields(): void
    {
        $user = $this->makeUser();
        $notification = ShfNotification::create([
            'user_id' => $user->id,
            'title' => 'Stage ready',
            'message' => 'You have a new task',
            'type' => 'assignment',
            'link' => '/loans/1/stages',
            'stage_key' => 'esign',
        ]);

        $payload = (new NotificationBroadcast($notification))->broadcastWith();

        $this->assertSame($notification->id, $payload['id']);
        $this->assertSame('Stage ready', $payload['title']);
        $this->assertSame('You have a new task', $payload['message']);
        $this->assertSame('assignment', $payload['type']);
        $this->assertSame('/loans/1/stages', $payload['link']);
        $this->assertSame('esign', $payload['stage_key']);
    }
}
