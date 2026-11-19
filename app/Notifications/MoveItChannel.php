<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class MoveItChannel
{
    public function send(User $user, Notification $notification): void
    {
        $user->notifications()->create($notification->toDatabase($user) + [
            'type' => $notification::class,
        ]);
    }
}
