<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\MoveItNotification;
use App\Models\PricingRule;
use App\Models\PlatformSetting;
use App\Models\Review;
use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BookingWorkflowController extends Controller
{
    public function vehicles(Request $request)
    {
        $window = $request->validate([
            'start' => ['bail', 'required', 'date', function ($attribute, $value, $fail) {
                if (!\Illuminate\Support\Carbon::parse($value, config('booking.timezone'))->isFuture()) {
                    $fail('Choose a booking time in the future (Amman time).');
                }
            }],

        ]);
        $start = \Illuminate\Support\Carbon::parse($window['start'], config('booking.timezone'))->utc();
        $end = Booking::slotEnd($start);
        $overlap = fn ($query) => $query->occupyingWindow($start, $end);

        $vehicles = Vehicle::where('status', 'active')->with('vehicleType:id,name')
            ->whereHas('vehicleType', fn ($query) => $query->where('is_active', true))
            ->whereHas('driver', fn ($query) => $query->where('status', 'approved')
                ->whereHas('user', fn ($user) => $user->where('is_active', true)))
            ->whereDoesntHave('bookings', $overlap)
            ->whereDoesntHave('driver.bookings', $overlap)
            ->orderBy('id')->paginate(20);

        $ratings = Review::join('bookings', 'reviews.booking_id', '=', 'bookings.id')
            ->where('bookings.status', 'completed')
            ->whereIn('bookings.driver_id', $vehicles->getCollection()->pluck('driver_id')->unique())
            ->selectRaw('bookings.driver_id, COUNT(*) as count, AVG(reviews.rating) as average')
            ->groupBy('bookings.driver_id')->get()->keyBy('driver_id');

        return response()->json($vehicles->through(fn (Vehicle $vehicle) => [
            ...$vehicle->only(['id', 'image', 'capacity_kg', 'capacity_m3', 'base_price', 'price_per_km']),
            'type' => $vehicle->vehicleType->name,
            'rating' => round((float) ($ratings->get($vehicle->driver_id)?->average ?? 0), 1),
            'reviews_count' => (int) ($ratings->get($vehicle->driver_id)?->count ?? 0),
        ]));
    }

    public function submit(Request $request, Booking $booking)
    {
        return $this->priceDraft($request, $booking, true);
    }

    public function quote(Request $request, Booking $booking)
    {
        return $this->priceDraft($request, $booking, false);
    }

    private function priceDraft(Request $request, Booking $booking, bool $submit)
    {
        abort_unless($booking->customer_id === $request->user()->id, 404);
        abort_unless($booking->status === 'draft', 409);
        $expected = $submit ? $request->validate(['expected_total' => 'sometimes|regex:/^\d{1,7}\.\d{3}$/D']) : [];

        $coordinates = $booking->only(['pickup_latitude', 'pickup_longitude', 'destination_latitude', 'destination_longitude']);
        abort_if(in_array(null, $coordinates, true), 422, 'Both locations are required.');
        abort_unless(PlatformSetting::bookingConfigured(), 503, 'Booking quotes are temporarily unavailable. Your draft is saved.');
        $meters = $this->routeDistance($coordinates);

        return DB::transaction(function () use ($booking, $coordinates, $meters, $submit, $expected) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);
            abort_unless($booking->status === 'draft' && $booking->only(array_keys($coordinates)) === $coordinates, 409);
            abort_unless($booking->scheduled_start_at?->isFuture(), 422, 'Choose a future booking time.');
            $booking->scheduled_end_at ??= Booking::slotEnd($booking->scheduled_start_at);
            abort_unless($booking->inventoryItems()->where('status', 'confirmed')->exists(), 422, 'Confirm at least one item.');

            $services = $booking->bookingServices()->with('service')->get();
            $types = $services->pluck('service.type');
            abort_unless($services->every(fn ($line) => $line->service?->is_active)
                && $types->filter(fn ($type) => $type === Service::TYPE_TRANSPORT)->count() === 1
                && (!$types->contains(Service::TYPE_ASSEMBLY) || $types->contains(Service::TYPE_WORKERS)),
                422, 'Review the selected services.');

            abort_unless($booking->vehicle_id, 422, 'Select a vehicle.');
            $vehicle = Vehicle::findOrFail($booking->vehicle_id);
            $driver = DriverProfile::with('user')->lockForUpdate()->findOrFail($vehicle->driver_id);
            $vehicle = Vehicle::with('vehicleType')->lockForUpdate()->findOrFail($vehicle->id);
            abort_unless($vehicle->driver_id === $driver->id, 409);
            $this->ensureAvailable($booking, $driver, $vehicle);

            $settings = PlatformSetting::values();
            $deposit = $this->percentage($settings['deposit_percent'], 0.01);
            $commission = $this->percentage($settings['commission_percent'], 0);
            $time = $booking->scheduled_start_at->copy()->timezone(config('booking.timezone'));
            $rules = PricingRule::where('is_active', true)
                ->where(fn ($query) => $query->whereNull('day_of_week')->orWhere('day_of_week', $time->dayOfWeek))
                ->where('start_time', '<=', $time->format('H:i:s'))
                ->where('end_time', '>', $time->format('H:i:s'))->limit(2)->get();
            abort_if($rules->count() > 1, 409, 'Conflicting pricing rules.');

            $transport = $this->milli($vehicle->base_price)
                + intdiv($this->milli($vehicle->price_per_km) * $meters + 500, 1000);
            $total = intdiv($transport * $this->milli($rules->first()?->multiplier ?? '1') + 500, 1000)
                + $services->sum(fn ($line) => $this->milli($line->unit_price) * $line->quantity);
            abort_unless($total > 0 && $total <= 9999999999, 422, 'The booking total is outside the supported range.');
            abort_if(isset($expected['expected_total']) && $expected['expected_total'] !== $this->formatMilli($total),
                409, 'The quote changed. Calculate it again before sending.');

            $values = [
                'driver_id' => $driver->id,
                'distance_km' => number_format($meters / 1000, 2, '.', ''),
                'total_price' => $this->formatMilli($total),
                'deposit_amount' => $this->formatMilli(max(1, intdiv($total * $deposit + 5000, 10000))),
                'commission_rate' => number_format($commission / 100, 2, '.', ''),
                'commission_amount' => $this->formatMilli(intdiv($total * $commission + 5000, 10000)),
                'status' => 'pending',
            ];
            if ($submit) {
                $booking->update($values);
                MoveItNotification::bookingEvent($driver->user_id, $booking, 'A new booking request was submitted.');
            }

            return response()->json([
                'id' => $booking->id, 'status' => $booking->status,
                ...\Illuminate\Support\Arr::only($values, ['distance_km', 'total_price', 'deposit_amount']),
            ]);
        }, 3);
    }

    public function accept(Request $request, Booking $booking)
    {
        return DB::transaction(function () use ($request, $booking) {
            $booking = $this->assignedBooking($request, $booking);
            if ($booking->status === 'awaiting_deposit') {
                return response()->json($booking->only(['id', 'status', 'deposit_due_at']));
            }
            abort_unless($booking->status === 'pending' && $booking->scheduled_start_at?->isFuture()
                && $booking->deposit_amount !== null, 409);
            $driver = DriverProfile::with('user')->lockForUpdate()->findOrFail($booking->driver_id);
            $vehicle = Vehicle::with('vehicleType')->lockForUpdate()->findOrFail($booking->vehicle_id);
            abort_unless($vehicle->driver_id === $driver->id, 409);
            $this->ensureAvailable($booking, $driver, $vehicle);

            $minutes = filter_var(PlatformSetting::values()['deposit_minutes'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            abort_unless($minutes !== false, 503, 'Deposit deadline is not configured.');
            $booking->update([
                'status' => 'awaiting_deposit',
                'deposit_due_at' => now()->addMinutes($minutes)->min($booking->scheduled_start_at),
            ]);
            MoveItNotification::bookingEvent($booking->customer_id, $booking, 'The driver accepted your booking.');

            return response()->json($booking->only(['id', 'status', 'deposit_due_at']));
        }, 3);
    }

    public function reject(Request $request, Booking $booking)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:1000']);

        return $this->transition($request, $booking, 'pending', 'rejected', $data);
    }

    public function start(Request $request, Booking $booking)
    {
        return $this->transition($request, $booking, 'confirmed', 'in_progress');
    }

    public function complete(Request $request, Booking $booking)
    {
        return $this->transition($request, $booking, 'in_progress', 'completed');
    }

    public function cancel(Request $request, Booking $booking)
    {
        abort_unless($booking->customer_id === $request->user()->id, 404);

        return DB::transaction(function () use ($booking) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);
            if ($booking->status !== 'cancelled') {
                abort_unless(in_array($booking->status, ['pending', 'awaiting_deposit'], true)
                    && !$booking->payments()->whereIn('status', ['pending', 'approved'])->exists(),
                    409, 'This cancellation requires admin review.');
                $booking->update(['status' => 'cancelled']);
                if ($booking->driver) {
                    MoveItNotification::bookingEvent($booking->driver->user_id, $booking, 'The customer cancelled the booking.');
                }
            }

            return response()->json($booking->only(['id', 'status']));
        }, 3);
    }

    private function transition(Request $request, Booking $booking, string $from, string $to, array $data = [])
    {
        return DB::transaction(function () use ($request, $booking, $from, $to, $data) {
            $booking = $this->assignedBooking($request, $booking);
            abort_unless($booking->status === $from || $booking->status === $to, 409);
            if ($booking->status === $from) {
                $booking->update($data + ['status' => $to]);
                $message = match ($to) {
                    'rejected' => 'The driver declined your booking.',
                    'in_progress' => 'Your move has started.',
                    'completed' => 'Your move is complete.',
                };
                MoveItNotification::bookingEvent($booking->customer_id, $booking, $message);
            }

            return response()->json(['booking_id' => $booking->id, 'status' => $booking->status]);
        }, 3);
    }

    private function assignedBooking(Request $request, Booking $booking): Booking
    {
        $driver = $request->user()->driverProfile;
        abort_unless($driver?->status === 'approved', 403);

        return $driver->bookings()->lockForUpdate()->findOrFail($booking->id);
    }

    private function ensureAvailable(Booking $booking, DriverProfile $driver, Vehicle $vehicle): void
    {
        abort_unless($driver->status === 'approved' && $driver->user?->is_active
            && $vehicle->status === 'active' && $vehicle->vehicleType?->is_active, 422, 'The vehicle is unavailable.');
        $conflict = Booking::whereKeyNot($booking->id)
            ->where(fn ($query) => $query->where('vehicle_id', $vehicle->id)->orWhere('driver_id', $driver->id))
            ->occupyingWindow($booking->scheduled_start_at, $booking->scheduled_end_at)
            ->lockForUpdate()->first(['id']);
        abort_if($conflict !== null, 409, 'The vehicle or driver is booked for that time.');
    }

    private function routeDistance(array $coordinates): int
    {
        $key = config('booking.google_routes_key');
        abort_unless($key, 503, 'Google Routes is not configured.');
        try {
            $response = Http::withHeaders(['X-Goog-Api-Key' => $key, 'X-Goog-FieldMask' => 'routes.distanceMeters'])
                ->timeout(8)->post('https://routes.googleapis.com/directions/v2:computeRoutes', [
                    'origin' => $this->waypoint($coordinates, 'pickup'),
                    'destination' => $this->waypoint($coordinates, 'destination'),
                    'travelMode' => 'DRIVE',
                ]);
        } catch (ConnectionException) {
            abort(503, 'Route calculation failed. Try again.');
        }
        $meters = $response->json('routes.0.distanceMeters');
        abort_unless($response->successful() && is_int($meters) && $meters > 0 && $meters <= 10000000,
            503, 'Route calculation failed. Try again.');

        return $meters;
    }

    private function waypoint(array $coordinates, string $prefix): array
    {
        return ['location' => ['latLng' => [
            'latitude' => (float) $coordinates[$prefix.'_latitude'],
            'longitude' => (float) $coordinates[$prefix.'_longitude'],
        ]]];
    }

    private function percentage($input, float $minimum): int
    {
        $value = (string) $input;
        abort_unless(preg_match('/^\d{1,3}(?:\.\d{1,2})?$/D', $value)
            && (float) $value >= $minimum && (float) $value <= 100, 503, 'Booking rates are not configured.');

        return intdiv($this->milli($value), 10);
    }

    private function milli(string $value): int
    {
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return (int) $whole * 1000 + (int) str_pad($fraction, 3, '0');
    }

    private function formatMilli(int $value): string
    {
        return sprintf('%d.%03d', intdiv($value, 1000), $value % 1000);
    }
}
