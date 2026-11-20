<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use RuntimeException;

class MoveItChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (method_exists($notification, 'toDatabase')) {
            $data = $notification->toDatabase($notifiable);
        } elseif (method_exists($notification, 'toArray')) {
            $data = $notification->toArray($notifiable);
        } else {
            throw new RuntimeException('Notification is missing a toDatabase / toArray method.');
        }

        if (! is_array($data)) {
            throw new RuntimeException('Notification data must be an array.');
        }

        $notifiable->notifications()->create([
            'type' => $notification::class,
            'data' => $data,
        ]);
    }
}
