<?php

namespace App\Notifications;

use App\Models\ShfNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Sends a ShfNotification through the Web Push channel so it shows up as a
 * native OS notification on desktop and mobile — even when the PWA is closed.
 *
 * The database row is already created by the ShfNotification model; this
 * Notification class only handles the browser-side push delivery.
 */
class ShfPushNotification extends Notification
{
    use Queueable;

    public function __construct(public ShfNotification $record) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, Notification $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->record->title)
            ->icon('/images/icon-192x192.png')
            ->badge('/images/icon-192x192.png')
            ->body($this->record->message)
            ->tag('shf-notification-'.$this->record->id)
            ->data([
                'url' => $this->record->link ?: '/dashboard',
                'notification_id' => $this->record->id,
                'type' => $this->record->type,
            ])
            ->options(['TTL' => 3600]);
    }
}
