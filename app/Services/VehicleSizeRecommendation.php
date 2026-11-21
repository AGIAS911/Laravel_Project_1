<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\VehicleType;
use Illuminate\Support\Collection;

class VehicleSizeRecommendation
{
    public function options(Booking $booking): array
    {
        $vehicleTypes = VehicleType::where('is_active', true)->orderBy('name')
            ->with(['vehicles' => fn ($query) => $query->eligibleForBooking()
                ->select('id', 'vehicle_type_id', 'capacity_kg', 'capacity_m3')])
            ->get();
        $estimate = $this->totals($booking->inventoryItems()->where('status', 'confirmed')->get());
        $limits = config('inventory.size_limits');

        $recommendedTypes = $vehicleTypes->filter(fn ($type) => $estimate['weight'] > 0
            && $type->vehicles->contains(fn ($vehicle) =>
                ($limits[$vehicle->capacity_kg][0] ?? 0) >= $estimate['weight']
                && ($limits[$vehicle->capacity_m3][1] ?? 0) >= $estimate['volume']))
            ->modelKeys();

        return compact('vehicleTypes', 'estimate', 'recommendedTypes');
    }

    private function totals(Collection $items): array
    {
        $weight = $volume = 0;
        foreach ($items as $item) {
            [$itemWeight, $itemVolume] = $item->estimate();
            $weight += $itemWeight * $item->quantity;
            $volume += $itemVolume * $item->quantity;
        }

        return compact('weight', 'volume');
    }
}
