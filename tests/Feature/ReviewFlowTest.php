<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_reviews_for_completed_driver_bookings(): void
    {
        $this->seed();
        $this->seed(\Database\Seeders\ReviewSeeder::class);

        $this->assertSame(15, Review::count());
        $this->assertSame(5, DriverProfile::count());
        $this->assertSame(15, Booking::where('status', 'completed')->count());

        Review::with('booking')->get()->each(function (Review $review) {
            $this->assertSame('completed', $review->booking->status);
            $this->assertGreaterThanOrEqual(1, $review->rating);
            $this->assertLessThanOrEqual(5, $review->rating);
            $this->assertNotEmpty($review->comment);
        });
    }

    public function test_assigned_driver_can_list_and_complete_booking(): void
    {
        $driver = DriverProfile::factory()->create();
        $otherDriver = DriverProfile::factory()->create();
        $booking = $this->createBooking($driver, User::factory()->create());

        $this->actingAs($driver->user)
            ->getJson(route('driver.bookings.index'))
            ->assertOk()
            ->assertJsonPath('bookings.0.id', $booking->id);

        $this->actingAs($otherDriver->user)
            ->patchJson(route('driver.bookings.complete', $booking))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'draft',
        ]);

        $this->actingAs($driver->user)
            ->patchJson(route('driver.bookings.complete', $booking))
            ->assertOk()
            ->assertJsonPath('status', 'completed');
    }

    public function test_customer_can_review_completed_booking_and_only_admin_sees_comment(): void
    {
        $driver = DriverProfile::factory()->create();
        $customer = User::factory()->create();
        $booking = $this->createBooking($driver, $customer);

        $this->actingAs($customer)
            ->getJson(route('booking.reviews.show', $booking))
            ->assertOk()
            ->assertJsonPath('can_review', false);

        $this->actingAs($customer)
            ->postJson(route('booking.reviews.store', $booking), [
                'rating' => 5,
                'comment' => 'Please review this driver assignment.',
            ])
            ->assertStatus(409);

        $this->actingAs($driver->user)
            ->patchJson(route('driver.bookings.complete', $booking))
            ->assertOk();

        $this->actingAs($customer)
            ->getJson(route('booking.reviews.show', $booking))
            ->assertOk()
            ->assertJsonPath('can_review', true);

        $this->actingAs($customer)
            ->postJson(route('booking.reviews.store', $booking), [
                'rating' => 5,
                'comment' => 'Please review this driver assignment.',
            ])
            ->assertCreated()
            ->assertJsonPath('review.rating', 5)
            ->assertJsonMissingPath('review.comment');

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Please review this driver assignment.',
        ]);

        $this->actingAs($customer)
            ->getJson(route('booking.reviews.show', $booking))
            ->assertOk()
            ->assertJsonPath('review.rating', 5)
            ->assertJsonMissingPath('review.comment');

        $this->actingAs($customer)
            ->postJson(route('booking.reviews.store', $booking), [
                'rating' => 4,
            ])
            ->assertStatus(409);

        $this->getJson(route('drivers.show', $driver))
            ->assertOk()
            ->assertJsonPath('ratings.0.rating', 5)
            ->assertJsonMissingPath('ratings.0.comment');

        $admin = User::factory()->state(['role' => 'admin'])->create();

        $this->actingAs($admin)
            ->get(route('admin.driver-reviews.index'))
            ->assertOk()
            ->assertSee($driver->user->name)
            ->assertSee('5.0 / 5');

        $this->actingAs($admin)
            ->get(route('admin.drivers.reviews.index', $driver))
            ->assertOk()
            ->assertSee('Please review this driver assignment.');

        $this->actingAs($admin)
            ->getJson(route('admin.drivers.reviews.index', $driver))
            ->assertOk()
            ->assertJsonPath('reviews.0.rating', 5)
            ->assertJsonPath('reviews.0.comment', 'Please review this driver assignment.');

        $this->actingAs($customer)
            ->getJson(route('admin.drivers.reviews.index', $driver))
            ->assertForbidden();

        $this->actingAs($customer)
            ->putJson(route('booking.reviews.update', $booking), [
                'rating' => 4,
                'comment' => 'Updated report comment.',
            ])
            ->assertOk()
            ->assertJsonPath('review.rating', 4)
            ->assertJsonMissingPath('review.comment');

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'rating' => 4,
            'comment' => 'Updated report comment.',
        ]);

        $this->actingAs($customer)
            ->deleteJson(route('booking.reviews.destroy', $booking))
            ->assertNoContent();

        $this->assertDatabaseMissing('reviews', ['booking_id' => $booking->id]);
    }

    private function createBooking(DriverProfile $driver, User $customer): Booking
    {
        return Booking::create([
            'customer_id' => $customer->id,
            'driver_id' => $driver->id,
        ]);
    }
}
