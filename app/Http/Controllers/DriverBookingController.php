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
        if ($driverProfile === null) {
            return response()->json(['message' => 'A driver profile is required.'], 403);
        }

        $bookings = Booking::with(['customer', 'vehicle'])
            ->where(function ($query) use ($driverProfile) {
                $query->where('driver_id', $driverProfile->id)
                    ->orWhereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery->where('driver_id', $driverProfile->id));
            })
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

    public function complete(Request $request, Booking $booking): JsonResponse
    {
        $driverProfile = $request->user()->driverProfile;
        if ($driverProfile === null) {
            return response()->json(['message' => 'A driver profile is required.'], 403);
        }

        $booking->loadMissing('vehicle');
        $assignedDriverId = $booking->driver_id ?? $booking->vehicle?->driver_id;

        if ((int) $assignedDriverId !== $driverProfile->id) {
            return response()->json(['message' => 'This booking is not assigned to you.'], 403);
        }

        if ($booking->status === 'completed') {
            return response()->json(['message' => 'This booking is already completed.'], 409);
        }

        $booking->update([
            'driver_id' => $driverProfile->id,
            'status' => 'completed',
        ]);

        return response()->json([
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]);
    }
}
