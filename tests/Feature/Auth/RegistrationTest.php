<?php

namespace Tests\Feature\Auth;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertDontSee('name="phone"')
            ->assertSee('name="account_type"', false);
        $this->assertFalse(Schema::hasColumn('users', 'phone'));
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'account_type' => 'customer',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'customer',
            'is_active' => true,
        ]);
    }

    public function test_driver_registration_creates_a_pending_driver_profile(): void
    {
        Storage::fake('public');
        $licenseImage = UploadedFile::fake()->createWithContent(
            'license.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lrcAAAAASUVORK5CYII=')
        );

        $response = $this->post('/register', [
            'name' => 'Test Driver',
            'email' => 'driver@example.com',
            'account_type' => 'driver',
            'license_number' => 'LIC-123',
            'license_image' => $licenseImage,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $driver = User::where('email', 'driver@example.com')->firstOrFail();
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $driver->id,
            'role' => 'driver',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('driver_profiles', [
            'user_id' => $driver->id,
            'license_number' => 'LIC-123',
            'status' => 'pending',
        ]);
        Storage::disk('public')->assertExists($driver->driverProfile->license_image);
    }
}
