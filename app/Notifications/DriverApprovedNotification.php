<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DriverApprovedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Your account as a driver has been approved',
            'message' =>  'You can now use the driver account.',
            'url' => route('home'),
        ];
    }
}
