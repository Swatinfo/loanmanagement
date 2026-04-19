<?php

namespace App\Events;

use App\Models\ShfNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ShfNotification is created. Broadcasts on the user's private
 * channel so the bell badge can refresh without polling.
 *
 * ShouldBroadcastNow (not ShouldBroadcast) so the push fires synchronously
 * in the creating request — no queue worker dependency for dev, and still
 * fast enough for prod since Reverb receives it directly.
 */
class NotificationBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ShfNotification $notification) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.'.$this->notification->user_id);
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'type' => $this->notification->type,
            'link' => $this->notification->link,
            'loan_id' => $this->notification->loan_id,
            'stage_key' => $this->notification->stage_key,
            'created_at' => $this->notification->created_at?->toIso8601String(),
        ];
    }
}
