<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DriverProfile;
use App\Notifications\DriverApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_approval_sends_one_database_notification_to_the_driver(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = User::factory()->create([
            'role' => 'driver',
            'is_active' => false,
        ]);
        DriverProfile::factory()->for($driver, 'user')->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $driver), ['approve_driver' => '1'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'type' => DriverApprovedNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $driver->id,
        ]);
        $this->assertTrue($driver->fresh()->is_active);

        $this->put(route('admin.users.update', $driver), ['approve_driver' => '1'])
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_only_admins_can_open_the_driver_approval_page(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_users_can_only_mark_their_own_notifications_as_read(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $otherUser = User::factory()->create();
        $driver->notify(new DriverApprovedNotification());
        $notification = $driver->notifications()->firstOrFail();

        $this->actingAs($driver)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Your account as a driver has been approved');

        $this->actingAs($otherUser)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->actingAs($driver)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
