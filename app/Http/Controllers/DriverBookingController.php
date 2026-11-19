<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverBookingController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $driverProfile = $request->user()->driverProfile;
        if ($driverProfile?->status !== 'approved') {
            abort(403, 'An approved driver profile is required.');
        }

        $bookings = Booking::with(['customer', 'vehicle'])
            ->where('driver_id', $driverProfile->id)
            ->where('status', '!=', 'draft')
            ->when($request->query('view') === 'requests', fn ($query) => $query->where('status', 'pending'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if ($request->prefers(['application/json', 'text/html']) === 'text/html') {
            return view('driver.bookings', compact('bookings'));
        }

        return response()->json([
            'bookings' => $bookings->getCollection()
                ->map(fn (Booking $booking) => [
                    'id' => $booking->id,
                    'status' => $booking->status,
                    'customer_name' => $booking->customer->name,
                    'vehicle' => $booking->vehicle === null ? null : [
                        'id' => $booking->vehicle->id,
                        'name' => $booking->vehicle->name,
                        'plate_number' => $booking->vehicle->plate_number,
                    ],
                    'created_at' => $booking->created_at,
                ])
                ->values(),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function schedule(Request $request): View
    {
        $driver = $request->user()->driverProfile;
        abort_unless($driver?->status === 'approved', 403);
        $bookings = $driver->bookings()->whereIn('status', ['awaiting_deposit', 'confirmed', 'in_progress'])
            ->where('scheduled_end_at', '>=', now()->startOfDay())->orderBy('scheduled_start_at')->paginate(20);

        return view('driver.schedule', compact('bookings'));
    }
}
