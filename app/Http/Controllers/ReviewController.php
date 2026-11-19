<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function show(Request $request, Booking $booking): JsonResponse
    {
        $booking = $request->user()->bookings()
            ->with(['review', 'vehicle'])
            ->findOrFail($booking->id);
        $driverProfileId = $booking->driver_id ?? $booking->vehicle?->driver_id;

        return response()->json([
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'can_review' => $booking->status === 'completed' && $booking->review === null,
            'driver_profile_url' => $driverProfileId === null ? null : route('drivers.show', $driverProfileId),
            'review' => $booking->review === null ? null : [
                'id' => $booking->review->id,
                'rating' => $booking->review->rating,
            ],
        ]);
    }

    public function store(Request $request, Booking $booking): JsonResponse
    {
        $booking = $request->user()->bookings()
            ->with(['review', 'vehicle'])
            ->findOrFail($booking->id);

        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'Reviews are available after the booking is completed.'], 409);
        }

        if ($booking->review !== null) {
            return response()->json(['message' => 'This booking already has a review.'], 409);
        }

        if (($booking->driver_id ?? $booking->vehicle?->driver_id) === null) {
            return response()->json(['message' => 'This booking has no assigned driver.'], 409);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = $booking->review()->create($validated);

        return response()->json([
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'created_at' => $review->created_at,
            ],
        ], 201);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $booking = $request->user()->bookings()
            ->with('review')
            ->where('status', 'completed')
            ->findOrFail($booking->id);

        if ($booking->review === null) {
            return response()->json(['message' => 'No review exists for this booking.'], 404);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->review->update($validated);

        return response()->json([
            'review' => [
                'id' => $booking->review->id,
                'rating' => $booking->review->rating,
                'created_at' => $booking->review->created_at,
            ],
        ]);
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        $booking = $request->user()->bookings()
            ->with('review')
            ->where('status', 'completed')
            ->findOrFail($booking->id);

        if ($booking->review === null) {
            return response()->json(['message' => 'No review exists for this booking.'], 404);
        }

        $booking->review->delete();

        return response()->json([], 204);
    }

    public function adminIndex(Request $request, DriverProfile $driverProfile): JsonResponse|View
    {
        if ($request->user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            abort(403);
        }

        $driverProfile->load('user');
        $reviews = Review::forDriver($driverProfile->id)
            ->with('booking.customer')
            ->latest()
            ->paginate(20);

        if (! $request->expectsJson()) {
            return view('admin.drivers.reviews', compact('driverProfile', 'reviews'));
        }

        return response()->json([
            'reviews' => $reviews->getCollection()
                ->map(fn (Review $review) => [
                    'id' => $review->id,
                    'booking_id' => $review->booking_id,
                    'customer_name' => $review->booking->customer->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                ])
                ->values(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function adminDrivers(): View
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $drivers = DriverProfile::with('user')
            ->orderBy('id')
            ->paginate(20);

        $drivers->getCollection()->each(function (DriverProfile $driver) {
            $summary = Review::forDriver($driver->id)
                ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as average_rating')
                ->first();

            $driver->setAttribute('reviews_count', (int) $summary->reviews_count);
            $driver->setAttribute(
                'average_rating',
                $summary->average_rating === null ? null : round((float) $summary->average_rating, 1)
            );
        });

        return view('admin.drivers.index', compact('drivers'));
    }
}
