<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverBookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $driverProfile = $request->user()->driverProfile;
        if ($driverProfile?->status !== 'approved') {
            return response()->json(['message' => 'A driver profile is required.'], 403);
        }

        $bookings = Booking::with(['customer', 'vehicle'])
            ->where('driver_id', $driverProfile->id)
            ->where('status', '!=', 'draft')
            ->latest()
            ->paginate(20);

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

}
