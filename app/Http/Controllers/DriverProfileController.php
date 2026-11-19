<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class DriverProfileController extends Controller
{
    public function show(DriverProfile $driverProfile): JsonResponse
    {
        $driverProfile->load('user');

        $query = Review::forDriver($driverProfile->id);
        $summary = (clone $query)
            ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as average_rating')
            ->first();

        $ratings = Review::forDriver($driverProfile->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'driver' => [
                'id' => $driverProfile->id,
                'name' => $driverProfile->user->name,
            ],
            'average_rating' => round((float) ($summary->average_rating ?? 0), 1),
            'reviews_count' => (int) $summary->reviews_count,
            'ratings' => $ratings->getCollection()
                ->map(fn (Review $review) => [
                    'rating' => $review->rating,
                    'created_at' => $review->created_at,
                ])
                ->values(),
            'pagination' => [
                'current_page' => $ratings->currentPage(),
                'last_page' => $ratings->lastPage(),
                'per_page' => $ratings->perPage(),
                'total' => $ratings->total(),
            ],
        ]);
    }
}
