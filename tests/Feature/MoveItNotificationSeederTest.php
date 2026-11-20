<?php

namespace Tests\Feature;

use App\Models\MoveItNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoveItNotificationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_seeder_creates_user_associated_notifications(): void
    {
        User::factory()->create(['role' => 'admin']);
        $customers = User::factory()->count(2)->create(['role' => 'customer']);

        $this->seed(\Database\Seeders\MoveItNotificationSeeder::class);

        $this->assertDatabaseCount('notifications', 10);

        $notification = MoveItNotification::firstOrFail();
        $this->assertSame($notification->user_id, $notification->notifiable_id);
        $this->assertSame($notification->data['title'], $notification->title);
        $this->assertSame($notification->data['message'], $notification->message);

        foreach ($customers as $customer) {
            $this->assertSame(5, $customer->notifications()->count());
        }

        $this->actingAs($customers->last())
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertDontSee('No notifications yet.');
    }

    public function test_customer_dashboard_displays_recent_notification_details(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $notification = MoveItNotification::factory()->forUser($user)->create([
            'data' => [
                'title' => 'Booking update',
                'message' => 'Your booking is confirmed.',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee($notification->title)
            ->assertSee($notification->message);
    }
}
