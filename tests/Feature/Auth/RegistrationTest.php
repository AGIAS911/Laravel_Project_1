<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0790000000',
            'account_type' => 'customer',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_driver_registers_then_can_open_workspace_after_approval(): void
    {
        Storage::fake('local');

        $this->post('/register', [
            'name' => 'Test Driver',
            'email' => 'driver@example.test',
            'phone' => '0790000000',
            'account_type' => 'driver',
            'password' => 'password',
            'password_confirmation' => 'password',
            'license_number' => 'TEST-123',
            'license_image' => new UploadedFile(base_path('ai-service/chair.jpg'), 'license.jpg', 'image/jpeg', null, true),
        ])->assertRedirect(route('driver.application.pending'));

        $this->assertGuest();
        $driver = User::where('email', 'driver@example.test')->firstOrFail();
        $this->assertFalse($driver->is_active);
        $this->assertSame('pending', $driver->driverProfile->status);
        Storage::disk('local')->assertExists($driver->driverProfile->license_image);

        $credentials = ['email' => $driver->email, 'password' => 'password'];
        $this->get(route('driver.application.pending'))->assertOk()->assertSee('Your application is under review');
        $this->post('/login', $credentials)->assertRedirect(route('driver.application.pending'));
        $this->assertGuest();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('admin.users.driver-status', $driver), ['status' => 'approved'])
            ->assertRedirect();
        $this->post(route('logout'))->assertRedirect();

        $this->post('/login', $credentials)->assertRedirect(route('driver.dashboard'));
        $this->get(route('driver.dashboard'))->assertOk()->assertSee('Manage your moves');
        $this->get(route('driver.profile.show'))->assertOk()->assertSee('TEST-123');
        $this->get(route('driver.profile.edit'))->assertOk();
        $this->get(route('driver.bookings.index', ['view' => 'requests']), ['Accept' => 'text/html'])
            ->assertOk()->assertSee('New requests');
        $this->get(route('driver.schedule'))->assertOk();
        $this->get(route('vehicles.index'))->assertOk();
    }

    public function test_existing_driver_without_profile_can_submit_license_for_approval(): void
    {
        Storage::fake('local');
        $driver = User::factory()->create(['role' => 'driver', 'is_active' => true]);

        $this->actingAs($driver)->get(route('driver.dashboard'))
            ->assertRedirect(route('driver.profile.edit'));
        $this->get(route('driver.profile.edit'))->assertOk()->assertSee('Complete your driver profile');
        $this->get(route('vehicles.index'))->assertRedirect(route('driver.profile.edit'));
        $this->put(route('driver.profile.update'), ['license_number' => 'OLD-123'])
            ->assertSessionHasErrors('license_image');
        $this->put(route('driver.profile.update'), [
            'license_number' => 'OLD-123',
            'license_image' => new UploadedFile(base_path('ai-service/chair.jpg'), 'license.jpg', 'image/jpeg', null, true),
        ])->assertRedirect(route('driver.application.pending'));

        $this->assertGuest();
        $this->assertFalse($driver->fresh()->is_active);
        $this->assertSame('pending', $driver->driverProfile()->firstOrFail()->status);
    }

    public function test_pending_driver_cannot_enter_dashboard_even_if_marked_active(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'is_active' => true]);
        \App\Models\DriverProfile::factory()->for($driver, 'user')->create(['status' => 'pending']);

        $this->post('/login', ['email' => $driver->email, 'password' => 'password'])
            ->assertRedirect(route('driver.application.pending'));
        $this->assertGuest();

        $this->actingAs($driver)->get(route('driver.dashboard'))
            ->assertRedirect(route('driver.application.pending'));
        $this->assertGuest();
    }

    public function test_driver_can_edit_contact_details_without_losing_approval(): void
    {
        $profile = \App\Models\DriverProfile::factory()->create();

        $this->actingAs($profile->user)->put(route('driver.profile.update'), [
            'name' => 'Updated driver name',
            'license_number' => $profile->license_number,
        ])->assertRedirect(route('driver.profile.show'));

        $this->assertAuthenticatedAs($profile->user);
        $this->assertTrue($profile->user->fresh()->is_active);
        $this->assertSame('Updated driver name', $profile->user->fresh()->name);
        $this->assertSame('approved', $profile->fresh()->status);
    }

    public function test_confirmed_move_stays_with_current_driver_during_license_update(): void
    {
        Storage::fake('local');
        $profile = \App\Models\DriverProfile::factory()->create();
        \App\Models\Booking::factory()->for($profile, 'driver')->create(['status' => 'confirmed']);

        $this->actingAs($profile->user)->put(route('driver.profile.update'), [
            'license_number' => 'NEW-456',
            'license_image' => new UploadedFile(base_path('ai-service/chair.jpg'), 'license.jpg', 'image/jpeg', null, true),
        ])->assertSessionHasErrors('license_image');

        $this->assertAuthenticatedAs($profile->user);
        $this->assertSame('approved', $profile->fresh()->status);
        Storage::disk('local')->assertDirectoryEmpty('licenses');
    }

    public function test_new_license_returns_approved_driver_to_review(): void
    {
        Storage::fake('local');
        $profile = \App\Models\DriverProfile::factory()->create();
        Storage::disk('local')->put($profile->license_image, 'old license');

        $this->actingAs($profile->user)->put(route('driver.profile.update'), ['license_number' => 'NEW-123'])
            ->assertSessionHasErrors('license_image');

        $this->put(route('driver.profile.update'), [
            'license_number' => 'NEW-123',
            'license_image' => new UploadedFile(base_path('ai-service/chair.jpg'), 'license.jpg', 'image/jpeg', null, true),
        ])->assertRedirect(route('driver.application.pending'));

        $this->assertGuest();
        $this->assertFalse($profile->user->refresh()->is_active);
        Storage::disk('local')->assertMissing($profile->license_image);
        $this->assertSame('pending', $profile->refresh()->status);
        Storage::disk('local')->assertExists($profile->license_image);
    }
}
