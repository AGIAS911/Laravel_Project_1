<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use RuntimeException;

class MoveItChannel
{
    public function send($notifiable, Notification $notification)
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

        return $notifiable->notifications()->create(
            [
                'booking_id' => $data['booking_id'] ?? null,
                'type' => $notification::class,
                'title' => $data['title'] ?? null,
                'message' => $data['message'] ?? null,
                'url' => $data['url'] ?? null,
            ]
        );
    }
}