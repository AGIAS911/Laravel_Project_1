<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\MoveItNotification;
use App\Models\PricingRule;
use App\Models\PlatformSetting;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\PaidBookingMatcher;
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
            'vehicle_type_id' => ['sometimes', 'integer', 'exists:vehicle_types,id'],
            'booking_id' => ['sometimes', 'integer'],
        ]);
        $start = \Illuminate\Support\Carbon::parse($window['start'], config('booking.timezone'))->utc();
        $draft = isset($window['booking_id'])
            ? $request->user()->bookings()->where('status', 'draft')->findOrFail($window['booking_id'])
            : null;
        $typeId = $draft?->preferred_vehicle_type_id ?? ($window['vehicle_type_id'] ?? null);
        $end = Booking::slotEnd($start);
        $available = Vehicle::availableAt($start, $end)
            ->when($typeId, fn ($query) => $query->where('vehicle_type_id', $typeId))
            ->exists();

        return response()->json(['available' => $available]);
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
        $expected = $submit ? $request->validate([
            'expected_total' => [$booking->booking_method ? 'required' : 'sometimes', 'regex:/^\d{1,7}\.\d{3}$/D'],
        ]) : [];

        $coordinates = $booking->only(['pickup_latitude', 'pickup_longitude', 'destination_latitude', 'destination_longitude']);
        abort_if(in_array(null, $coordinates, true), 422, 'Both locations are required.');
        abort_unless(PlatformSetting::quoteConfigured(), 503, 'Route pricing is temporarily unavailable. Your draft is saved.');
        $meters = $this->routeDistance($coordinates);

        return DB::transaction(function () use ($booking, $coordinates, $meters, $submit, $expected) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);
            abort_unless($booking->status === 'draft' && $booking->only(array_keys($coordinates)) === $coordinates, 409);
            abort_unless($booking->scheduled_start_at?->isFuture(), 422, 'Choose a future booking time.');
            $settings = PlatformSetting::values();
            abort_if($submit && !PlatformSetting::bookingConfigured($settings), 503, 'CliQ checkout is temporarily unavailable. Your draft is saved.');
            $booking->scheduled_end_at ??= Booking::slotEnd($booking->scheduled_start_at);
            abort_unless(!$booking->needsInventory() || $booking->inventoryItems()->where('status', 'confirmed')->exists(),
                422, 'Confirm at least one item.');

            $services = $booking->bookingServices()->with('service')->get();
            $types = $services->pluck('service.type');
            abort_unless($services->every(fn ($line) => $line->service?->is_active)
                && $types->filter(fn ($type) => $type === Service::TYPE_TRANSPORT)->count() === 1
                && (!$types->contains(Service::TYPE_ASSEMBLY) || $types->contains(Service::TYPE_WORKERS)),
                422, 'Review the selected services.');

            $typeId = $booking->preferred_vehicle_type_id ?? ($booking->booking_method ? null : $booking->vehicle?->vehicle_type_id);
            abort_unless($typeId, 422, 'Choose a vehicle type.');
            $vehicle = Vehicle::availableAt($booking->scheduled_start_at, $booking->scheduled_end_at)
                ->where('vehicle_type_id', $typeId)
                ->orderByRaw('base_price + price_per_km * ?', [$meters / 1000])
                ->orderBy('id')
                ->first();
            abort_unless($vehicle, 409, 'No vehicle of this type is available for your appointment. Try another time or vehicle type.');

            $total = $this->transportPrice($vehicle, $meters, $this->timeMultiplier($booking->scheduled_start_at))
                + $services->sum(fn ($line) => $this->milli($line->unit_price) * $line->quantity);
            abort_unless($total > 0 && $total <= 9999999999, 422, 'The booking total is outside the supported range.');
            abort_if(isset($expected['expected_total']) && $expected['expected_total'] !== $this->formatMilli($total),
                409, 'The quote changed. Calculate it again before sending.');

            $deposit = filled($settings['deposit_percent']) ? $this->percentage($settings['deposit_percent'], 0.01) : null;
            $quote = [
                'distance_km' => number_format($meters / 1000, 2, '.', ''),
                'total_price' => $this->formatMilli($total),
                'deposit_amount' => $deposit === null ? null : $this->formatMilli(max(1, intdiv($total * $deposit + 5000, 10000))),
            ];
            if ($submit) {
                $commission = $this->percentage($settings['commission_percent'], 0);
                $booking->update($quote + [
                    'vehicle_id' => null,
                    'driver_id' => null,
                    'preferred_vehicle_type_id' => $typeId,
                    'commission_rate' => number_format($commission / 100, 2, '.', ''),
                    'commission_amount' => $this->formatMilli(intdiv($total * $commission + 5000, 10000)),
                    'status' => 'awaiting_deposit',
                    'deposit_due_at' => $this->depositDeadline($booking, $settings),
                ]);
            }

            return response()->json([
                'id' => $booking->id, 'status' => $booking->status,
                ...($submit ? ['payment_url' => route('payments.create', ['booking_id' => $booking->id])] : []),
                ...$quote,
            ]);
        }, 3);
    }

    public function accept(Request $request, Booking $booking)
    {
        return DB::transaction(function () use ($request, $booking) {
            $booking = $this->assignedBooking($request, $booking);
            if ($booking->status === 'awaiting_deposit' || ($booking->status === 'confirmed' && $booking->deposit_verified_at)) {
                return response()->json($booking->only(['id', 'status', 'deposit_due_at']));
            }
            abort_unless($booking->status === 'pending' && $booking->scheduled_start_at?->isFuture()
                && $booking->deposit_amount !== null, 409);
            abort_if($booking->deposit_verified_at && !$booking->driver_offer_expires_at?->isFuture(),
                409, 'This booking offer expired.');
            $driver = DriverProfile::with('user')->lockForUpdate()->findOrFail($booking->driver_id);
            $vehicle = Vehicle::with('vehicleType')->lockForUpdate()->findOrFail($booking->vehicle_id);
            abort_unless($vehicle->driver_id === $driver->id, 409);
            $this->ensureAvailable($booking, $driver, $vehicle);

            if ($booking->deposit_verified_at) {
                $booking->update(['status' => 'confirmed', 'driver_offer_expires_at' => null]);
                MoveItNotification::bookingEvent($booking->customer_id, $booking, 'Your driver accepted. Vehicle and driver details are now available.');
            } else {
                $booking->update(['status' => 'awaiting_deposit', 'deposit_due_at' => $this->depositDeadline($booking)]);
                MoveItNotification::bookingEvent($booking->customer_id, $booking, 'The driver accepted your booking.');
            }

            return response()->json($booking->only(['id', 'status', 'deposit_due_at']));
        }, 3);
    }

    public function reject(Request $request, Booking $booking, PaidBookingMatcher $matcher)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:1000']);
        if (!$booking->deposit_verified_at) {
            return $this->transition($request, $booking, 'pending', 'rejected', $data);
        }

        DB::transaction(function () use ($request, $booking, $matcher) {
            $current = $this->assignedBooking($request, $booking);
            abort_unless($current->status === 'pending', 409);
            $matcher->skipCurrentDriver($current);
        }, 3);
        $matcher->findDriver($booking->id);

        return response()->json(['booking_id' => $booking->id, 'status' => 'pending']);
    }

    public function retryDriverMatch(Request $request, Booking $booking, PaidBookingMatcher $matcher)
    {
        abort_unless($booking->customer_id === $request->user()->id, 404);
        abort_unless($booking->status === 'pending' && $booking->deposit_verified_at && $booking->scheduled_start_at?->isFuture(), 409);
        $matcher->findDriver($booking->id);

        return redirect()->route('booking.show', $booking);
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

    private function depositDeadline(Booking $booking, ?array $settings = null): \Illuminate\Support\Carbon
    {
        $settings ??= PlatformSetting::values();
        $minutes = filter_var($settings['deposit_minutes'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_unless($minutes !== false, 503, 'Deposit deadline is not configured.');

        return now()->addMinutes($minutes)->min($booking->scheduled_start_at);
    }

    private function timeMultiplier(\Illuminate\Support\Carbon $appointment): string
    {
        $time = $appointment->copy()->timezone(config('booking.timezone'));
        $rules = PricingRule::where('is_active', true)
            ->where(fn ($query) => $query->whereNull('day_of_week')->orWhere('day_of_week', $time->dayOfWeek))
            ->where('start_time', '<=', $time->format('H:i:s'))
            ->where('end_time', '>', $time->format('H:i:s'))->limit(2)->get();
        abort_if($rules->count() > 1, 409, 'Conflicting pricing rules.');

        return $rules->first()?->multiplier ?? '1';
    }

    private function transportPrice(Vehicle $vehicle, int $meters, string $multiplier): int
    {
        $transport = $this->milli($vehicle->base_price)
            + intdiv($this->milli($vehicle->price_per_km) * $meters + 500, 1000);

        return intdiv($transport * $this->milli($multiplier) + 500, 1000);
    }

    private function routeDistance(array $coordinates): int
    {
        $origin = sprintf('%.7F,%.7F', (float) $coordinates['pickup_longitude'], (float) $coordinates['pickup_latitude']);
        $destination = sprintf('%.7F,%.7F', (float) $coordinates['destination_longitude'], (float) $coordinates['destination_latitude']);
        $url = rtrim((string) config('booking.osrm_base_url'), '/')."/route/v1/driving/{$origin};{$destination}";

        try {
            $response = Http::timeout(8)->get($url, ['overview' => 'false']);
        } catch (ConnectionException) {
            abort(503, 'Route calculation failed. Try again.');
        }
        $meters = $response->json('routes.0.distance');
        abort_unless($response->successful() && is_numeric($meters) && $meters > 0 && $meters <= 10000000,
            503, 'Route calculation failed. Try again.');

        return (int) round((float) $meters);
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
