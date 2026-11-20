<?php

namespace Tests\Feature;

use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_factory_creates_a_database_notification(): void
    {
        $notification = Notification::factory()->create();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'notifiable_id' => $notification->notifiable_id,
        ]);
    }
}
