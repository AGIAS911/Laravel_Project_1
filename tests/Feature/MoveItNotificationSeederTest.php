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

    public function test_unread_filter_only_shows_unread_notifications_for_the_current_user(): void
    {
        $customer = User::factory()->create();
        $unread = MoveItNotification::factory()->forUser($customer)->create([
            'title' => 'Unread booking notice',
            'read_at' => null,
        ]);
        MoveItNotification::factory()->forUser($customer)->create([
            'title' => 'Previously read notice',
            'read_at' => now(),
        ]);
        MoveItNotification::factory()->create([
            'title' => 'Another customer notice',
            'read_at' => null,
        ]);

        $this->actingAs($customer)->get(route('notifications.index', ['filter' => 'unread']))
            ->assertOk()->assertSee('Unread booking notice')
            ->assertDontSee('Previously read notice')->assertDontSee('Another customer notice');

        $this->post(route('notifications.read', $unread->id))->assertRedirect();
        $this->get(route('notifications.index', ['filter' => 'unread']))
            ->assertOk()->assertSee('all caught up')->assertDontSee('Unread booking notice');
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
