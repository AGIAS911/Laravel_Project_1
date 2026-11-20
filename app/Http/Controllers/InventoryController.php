<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public const ITEM_TYPES = [
        'chair', 'sofa', 'bed', 'dining table', 'tv', 'refrigerator',
        'microwave', 'oven', 'laptop', 'potted plant', 'wardrobe', 'washing machine', 'desk', 'mirror', 'other',
    ];

    public function index(Request $request, Booking $booking)
    {
        abort_unless($booking->customer_id === $request->user()->id, 404);

        return response()->json($booking->inventoryItems()->orderBy('id')->get());
    }

    public function image(Request $request, Booking $booking, InventoryItem $inventoryItem)
    {
        abort_unless($booking->customer_id === $request->user()->id && $inventoryItem->image
            && Storage::disk('local')->exists($inventoryItem->image), 404);

        return Storage::disk('local')->response($inventoryItem->image, null, ['Cache-Control' => 'private, no-store']);
    }

    public function startBooking(Request $request)
    {
        return $this->save($request, function (array $data) use ($request) {
            $booking = $request->user()->bookings()->create(['status' => 'draft']);
            $booking->inventoryItems()->create(array_merge($data, ['source' => 'manual', 'status' => 'confirmed']));

            return redirect()->route('booking.show', $booking)->with('success', 'Item added to your booking draft.');
        });
    }

    public function store(Request $request, Booking $booking)
    {
        abort_unless($booking->customer_id === $request->user()->id, 404);

        return $this->save($request, function (array $data) use ($request, $booking) {
            $booking = $this->draft($request, $booking);
            $item = $booking->inventoryItems()->create($data + [
                'source' => 'manual', 'status' => 'confirmed',
            ]);

            return response()->json($item, 201);
        });
    }

    public function update(Request $request, Booking $booking, InventoryItem $inventoryItem)
    {
        abort_unless($booking->customer_id === $request->user()->id, 404);

        return $this->save($request, function (array $data) use ($request, $booking, $inventoryItem) {
            $booking = $this->draft($request, $booking);
            $inventoryItem = $booking->inventoryItems()->findOrFail($inventoryItem->id);
            $previousImage = $inventoryItem->image;
            $inventoryItem->update($data + ['status' => 'confirmed']);
            if (isset($data['image']) && $previousImage) {
                DB::afterCommit(fn () => Storage::disk('local')->delete($previousImage));
            }

            return response()->json($inventoryItem);
        });
    }

    public function destroy(Request $request, Booking $booking, InventoryItem $inventoryItem)
    {
        return DB::transaction(function () use ($request, $booking, $inventoryItem) {
            $booking = $this->draft($request, $booking);
            $inventoryItem = $booking->inventoryItems()->findOrFail($inventoryItem->id);
            $inventoryItem->delete();
            if ($inventoryItem->image) {
                DB::afterCommit(fn () => Storage::disk('local')->delete($inventoryItem->image));
            }

            return response()->noContent();
        });
    }

    private function draft(Request $request, Booking $booking): Booking
    {
        abort_unless($booking->customer_id === $request->user()->id, 404);
        $booking = Booking::lockForUpdate()->findOrFail($booking->id);
        abort_unless($booking->status === 'draft', 409);

        return $booking;
    }

    private function save(Request $request, callable $persist)
    {
        $data = $request->validate([
            'item_type' => ['required', Rule::in(self::ITEM_TYPES)],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', Rule::in(['confirmed', 'excluded'])],
            'image' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $path = null;
        try {
            return DB::transaction(function () use ($data, $persist, &$path) {
                if (isset($data['image'])) {
                    $path = $data['image']->store('inventory', 'local');
                    abort_unless($path, 503, 'Could not save the inventory image.');
                    $data['image'] = $path;
                }

                return $persist($data);
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }
    }
}
