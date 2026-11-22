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

    public function test_driver_dashboard_keeps_identity_and_links_in_sidebar_without_topbar(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        DriverProfile::factory()->for($driver, 'user')->create(['status' => 'approved']);

        $this->actingAs($driver)
            ->get(route('driver.dashboard'))
            ->assertOk()
            ->assertDontSee('moveit-topbar', false)
            ->assertSee('workspace-menu', false)
            ->assertSee('MOVE<span>IT</span>', false)
            ->assertSee($driver->name)
            ->assertSee(route('driver.dashboard'))
            ->assertSee(route('driver.bookings.index'))
            ->assertSee(route('driver.schedule'))
            ->assertSee(route('vehicles.index'))
            ->assertSee(route('services.index'))
            ->assertSee(route('driver.profile.show'))
            ->assertSee(route('driver.bookings.index', ['view' => 'requests']))
            ->assertSee(route('notifications.index'))
            ->assertSee(route('profile.edit'))
            ->assertSee('Log out');
    }

    public function test_users_can_only_mark_their_own_notifications_as_read(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $otherUser = User::factory()->create();
        $driver->notify(new DriverApprovedNotification());
        $notification = $driver->notifications()->firstOrFail();

        $this->actingAs($driver)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Your account as a driver has been approved')
            ->assertSee(route('notifications.open', $notification->id));

        $this->actingAs($otherUser)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->get(route('notifications.open', $notification->id))
            ->assertNotFound();

        $this->actingAs($driver)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('home'));

        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($driver)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
