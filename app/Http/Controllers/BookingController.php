<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\PlatformSetting;
use App\Models\Service;
use App\Models\VehicleType;
use App\Services\VehicleSizeRecommendation;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as LaravelCarbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $statuses = [];
        foreach (BookingStatus::cases() as $case) {
            $statuses[$case->value] = $case->filterLabel();
        }
        $requestedStatus = $request->query('status');
        $status = is_string($requestedStatus) ? BookingStatus::tryFrom($requestedStatus)?->value : null;
        $search = $request->query('q', '');
        $search = is_string($search) ? trim(substr($search, 0, 40)) : '';
        $query = $user->role === 'admin' ? Booking::query() : $user->bookings();
        $drivers = $user->role === 'admin'
            ? DriverProfile::with('user:id,name')->orderBy('id')->get(['id', 'user_id'])
            : collect();
        $driverFilter = $request->query('driver');
        $driverFilter = is_string($driverFilter) && $drivers->contains('id', $driverFilter) ? $driverFilter : null;
        $relations = $user->role === 'admin' ? ['customer', 'driver.user'] : ['vehicle.vehicleType', 'preferredVehicleType'];
        $bookings = $query->with($relations)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($driverFilter, fn ($query) => $query->where('driver_id', $driverFilter))
            ->when($search !== '', fn ($query) => $query->where('id', ctype_digit($search) && strlen($search) <= 18 ? $search : 0))
            ->latest('id')->paginate(10)->appends(array_filter(['status' => $status, 'q' => $search, 'driver' => $driverFilter]));

        return view('booking.index', compact('bookings', 'statuses', 'status', 'search', 'drivers', 'driverFilter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        abort_unless($request->user()->role === 'customer', 403);
        return view("booking.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'customer', 403);
        $request->validate(['vehicle_id' => ['prohibited'], 'driver_id' => ['prohibited']]);
        $data = $request->validate($this->bookingRules() + [
            'booking_method' => ['sometimes', Rule::in(['quick', 'manual', 'scan'])],
        ]);
        $data['customer_id'] = $request->user()->id;
        $data['status'] = BookingStatus::Draft->value;
        $data = $this->draftValues($data);

        $booking = Booking::create($data);

        $destination = match ($booking->booking_method) {
            'quick' => route('booking.edit', ['booking' => $booking, 'step' => 'type']),
            'manual' => route('booking.show', $booking).'#inventory',
            'scan' => route('scan-sessions.workspace', $booking),
            default => route($request->boolean('open_phone_scan') ? 'scan-sessions.workspace' : 'booking.show', $booking),
        };

        return redirect($destination)->with('success', 'Booking draft created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $booking = match ($user->role) {
            'admin' => Booking::findOrFail($id),
            'driver' => $user->driverProfile?->bookings()->where('status', '!=', BookingStatus::Draft->value)->findOrFail($id),
            default => $user->bookings()->findOrFail($id),
        };
        abort_unless($booking, 404);

        if ($request->expectsJson()) {
            $details = $booking->only([
                'id', 'status', 'distance_km', 'scheduled_start_at', 'scheduled_end_at',
                'total_price', 'deposit_amount', 'deposit_due_at', 'rejection_reason', 'notes',
                'pickup_floor', 'pickup_has_elevator', 'destination_floor', 'destination_has_elevator',
            ]);
            foreach (['pickup', 'destination'] as $stop) {
                $lat = $booking->{ $stop.'_latitude' };
                $lng = $booking->{ $stop.'_longitude' };
                $details[$stop] = $lat === null || $lng === null ? null : [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'navigation_url' => 'https://www.google.com/maps/dir/?'.http_build_query([
                        'api' => 1, 'destination' => $lat.','.$lng, 'travelmode' => 'driving',
                    ]),
                ];
            }
            if ($user->role === 'admin' || $booking->assignmentVisibleToCustomer()) {
                $details['driver'] = $booking->driver?->user?->only(['name', 'phone']);
                $vehicle = $booking->vehicle;
                $details['vehicle'] = $vehicle ? [
                    'name' => $vehicle->name,
                    'plate_number' => $vehicle->plate_number,
                    'type' => $vehicle->vehicleType?->name,
                    'photo_url' => $vehicle->image ? asset($vehicle->image) : null,
                ] : null;
            }

            return response()->json($details);
        }

        $booking->load(['inventoryItems', 'bookingServices.service', 'review', 'preferredVehicleType']);
        $services = $user->id === $booking->customer_id && $booking->status === BookingStatus::Draft->value
            ? Service::where('is_active', true)->orderBy('name')->get()
            : collect();

        $receiptUnderReview = $user->id === $booking->customer_id && $booking->status === BookingStatus::AwaitingDeposit->value
            && $booking->payments()->where('status', 'pending')->where('purpose', 'deposit')->exists();

        $cancellationRequiresReview = $user->id === $booking->customer_id
            && in_array($booking->status, [BookingStatus::Pending->value, BookingStatus::AwaitingDeposit->value], true)
            && ($receiptUnderReview || $booking->payments()->whereIn('status', ['pending', 'approved'])->exists());

        $cliqConfigured = $booking->status === BookingStatus::AwaitingDeposit->value && PlatformSetting::cliqConfigured();

        return view('booking.show', compact('booking', 'services', 'receiptUnderReview', 'cancellationRequiresReview', 'cliqConfigured'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id, VehicleSizeRecommendation $recommendation)
    {
        abort_unless($request->user()->role === 'customer', 403);
        $booking = $request->user()->bookings()->where('status', BookingStatus::Draft->value)->findOrFail($id);
        $step = null;

        if ($booking->booking_method) {
            if ($booking->needsInventory() && !$booking->inventoryItems()->where('status', 'confirmed')->exists()) {
                return redirect(route('booking.show', $booking).'#inventory');
            }
            $next = match (true) {
                !$booking->preferredVehicleType?->is_active => 'type',
                !$booking->hasRoute() => 'route',
                !$booking->scheduled_start_at?->isFuture() => 'time',
                default => 'review',
            };
            $requested = $request->query('step');
            $steps = ['type', 'route', 'time', 'review'];
            $step = in_array($requested, array_slice($steps, 0, array_search($next, $steps, true) + 1), true)
                ? $requested : $next;

            if ($step === 'type') {
                return view('booking.vehicle-type', ['booking' => $booking] + $recommendation->options($booking));
            }

            if ($step === 'review') {
                return redirect(route('booking.show', $booking).'#services');
            }
        }

        $vehicleTypes = $step === null ? VehicleType::where('is_active', true)->orderBy('name')->pluck('name', 'id') : collect();

        return view('booking.edit', compact('booking', 'step', 'vehicleTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_unless($request->user()->role === 'customer', 403);

        $step = $request->validate([
            'booking_step' => ['sometimes', Rule::in(['type', 'route', 'time'])],
            'vehicle_id' => ['prohibited'],
            'driver_id' => ['prohibited'],
        ])['booking_step'] ?? null;
        $rules = $this->bookingRules();
        $coordinates = ['pickup_latitude', 'pickup_longitude', 'destination_latitude', 'destination_longitude'];
        $rules = match ($step) {
            'type' => ['preferred_vehicle_type_id' => ['required', ...$rules['preferred_vehicle_type_id']]],
            'route' => array_map(fn ($rules) => ['required', ...$rules], array_intersect_key($rules, array_flip($coordinates)))
                + array_diff_key($rules, array_flip(['scheduled_start_at', 'preferred_vehicle_type_id', ...$coordinates])),
            'time' => ['scheduled_start_at' => ['required', ...$rules['scheduled_start_at']]],

            default => $rules,
        };
        $data = $this->draftValues($request->validate($rules));

        DB::transaction(function () use ($request, $id, $data, $step) {
            $booking = $request->user()->bookings()->where('status', BookingStatus::Draft->value)->lockForUpdate()->findOrFail($id);
            if ($booking->booking_method && !$step) {
                throw ValidationException::withMessages(['booking_step' => 'Continue from the current booking step.']);
            }
            if ($booking->booking_method && $booking->needsInventory()
                && !$booking->inventoryItems()->where('status', 'confirmed')->exists()) {
                throw ValidationException::withMessages(['booking_step' => 'Confirm at least one item before choosing a vehicle.']);
            }
            if (in_array($step, ['route', 'time'], true) && !$booking->preferredVehicleType?->is_active) {
                throw ValidationException::withMessages(['preferred_vehicle_type_id' => 'Choose an active vehicle type first.']);
            }
            if ($step === 'time' && !$booking->hasRoute()) {
                throw ValidationException::withMessages(['pickup_latitude' => 'Set both locations first.']);
            }
            $booking->fill($data);
            if ($booking->isDirty('preferred_vehicle_type_id')) {
                $booking->fill(['vehicle_id' => null, 'driver_id' => null]);
            }

            if ($step === 'time' && !$booking->bookingServices()->whereHas('service', fn ($query) => $query->where('type', Service::TYPE_TRANSPORT))->exists()) {
                $transport = Service::where('is_active', true)->where('type', Service::TYPE_TRANSPORT)->limit(2)->get();
                if ($transport->count() === 1) {
                    $service = $transport->first();
                    $booking->bookingServices()->create([
                        'service_id' => $service->id, 'service_name' => $service->name,
                        'unit' => $service->unit, 'quantity' => 1, 'unit_price' => $service->price,
                    ]);
                }
            }
            if ($booking->isDirty([
                'vehicle_id', 'preferred_vehicle_type_id', 'pickup_latitude', 'pickup_longitude',
                'destination_latitude', 'destination_longitude', 'scheduled_start_at', 'scheduled_end_at',
            ])) {
                $booking->fill([
                    'distance_km' => null, 'total_price' => null, 'deposit_amount' => null,
                    'commission_rate' => null, 'commission_amount' => null,
                ]);
            }
            $booking->save();
        });

        $next = ['type' => 'route', 'route' => 'time'][$step] ?? null;

        if ($next) {
            return redirect()->route('booking.edit', ['booking' => $id, 'step' => $next]);
        }

        return redirect(route('booking.show', $id).($step === 'time' ? '#services' : ''))
            ->with('success', 'Booking draft saved.');
    }

    private function bookingRules(): array
    {
        return [
            'preferred_vehicle_type_id' => ['nullable', 'integer', Rule::exists('vehicle_types', 'id')->where('is_active', true)],
            'pickup_latitude' => ['required_with:pickup_longitude', 'nullable', 'numeric', 'decimal:0,7', 'between:-90,90'],
            'pickup_longitude' => ['required_with:pickup_latitude', 'nullable', 'numeric', 'decimal:0,7', 'between:-180,180'],
            'destination_latitude' => ['required_with:destination_longitude', 'nullable', 'numeric', 'decimal:0,7', 'between:-90,90'],
            'destination_longitude' => ['required_with:destination_latitude', 'nullable', 'numeric', 'decimal:0,7', 'between:-180,180'],
            'pickup_floor' => ['sometimes', 'required', 'integer', 'between:-32768,32767'],
            'pickup_has_elevator' => ['sometimes', 'required', 'boolean'],
            'destination_floor' => ['sometimes', 'required', 'integer', 'between:-32768,32767'],
            'destination_has_elevator' => ['sometimes', 'required', 'boolean'],
            'scheduled_start_at' => ['nullable', 'bail', 'date', function ($attribute, $value, $fail) {
                if (!LaravelCarbon::parse($value, config('booking.timezone'))->isFuture()) {
                    $fail('Choose a booking time in the future (Amman time).');
                }
            }],

            'notes' => ['nullable', 'string', 'max:16383'],
        ];
    }

    private function draftValues(array $data): array
    {

        if (array_key_exists('scheduled_start_at', $data)) {
            $appointment = $data['scheduled_start_at'] === null
                ? null
                : LaravelCarbon::parse($data['scheduled_start_at'], config('booking.timezone'))->utc();

            $data['scheduled_start_at'] = $appointment;
            $data['scheduled_end_at'] = $appointment ? Booking::slotEnd($appointment) : null;
        }

        return $data;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        abort_unless($request->user()->role === 'customer', 403);
        DB::transaction(function () use ($request, $id) {
            $booking = $request->user()->bookings()->where('status', BookingStatus::Draft->value)->lockForUpdate()->findOrFail($id);
            $images = $booking->inventoryItems()->whereNotNull('image')->pluck('image')->all();
            $booking->inventoryItems()->delete();
            $booking->scanSessions()->delete();
            $booking->bookingServices()->delete();
            $booking->delete();
            if ($images) {
                DB::afterCommit(fn () => Storage::disk('local')->delete($images));
            }
        });
        return redirect()->route('booking.index')
            ->with('success', 'Booking deleted successfully');
    }
}
