<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\PlatformSetting;
use App\Models\Vehicle;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ScanSession;
use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\User;
use App\Models\MoveItNotification;
class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $statuses = [
            'draft' => 'Drafts',
            'pending' => 'Awaiting driver',
            'awaiting_deposit' => 'Awaiting deposit',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        $status = $request->query('status');
        $status = is_string($status) && isset($statuses[$status]) ? $status : null;
        $search = $request->query('q', '');
        $search = is_string($search) ? trim(substr($search, 0, 40)) : '';
        $query = $user->role === 'admin' ? Booking::query() : $user->bookings();
        $drivers = $user->role === 'admin'
            ? \App\Models\DriverProfile::with('user:id,name')->orderBy('id')->get(['id', 'user_id'])
            : collect();
        $driverFilter = $request->query('driver');
        $driverFilter = is_string($driverFilter) && $drivers->contains('id', $driverFilter) ? $driverFilter : null;
        $relations = $user->role === 'admin' ? ['customer', 'driver.user'] : ['vehicle.vehicleType'];
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
        $data = $request->validate($this->bookingRules());
        $data['customer_id'] = $request->user()->id;
        $data['status'] = 'draft';
        $data = $this->draftValues($data);

        $booking = Booking::create($data);

        $destination = $request->boolean('open_phone_scan') ? 'scan-sessions.workspace' : 'booking.show';

        return redirect()->route($destination, $booking)->with('success', 'Booking created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $booking = match ($user->role) {
            'admin' => Booking::findOrFail($id),
            'driver' => $user->driverProfile?->bookings()->where('status', '!=', 'draft')->findOrFail($id),
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
            if ($user->role === 'admin' || in_array($booking->status, ['confirmed', 'in_progress', 'completed'], true)) {
                $details['driver'] = $booking->driver?->user?->only(['name', 'phone']);
            }

            return response()->json($details);
        }

        $booking->load(['inventoryItems', 'bookingServices.service', 'review']);
        $services = $user->id === $booking->customer_id && $booking->status === 'draft'
            ? Service::where('is_active', true)->orderBy('name')->get()
            : collect();

        $receiptUnderReview = $user->id === $booking->customer_id && $booking->status === 'awaiting_deposit'
            && $booking->payments()->where('status', 'pending')->where('purpose', 'deposit')->exists();

        $cancellationRequiresReview = $user->id === $booking->customer_id
            && in_array($booking->status, ['pending', 'awaiting_deposit'], true)
            && ($receiptUnderReview || $booking->payments()->whereIn('status', ['pending', 'approved'])->exists());

        $cliqConfigured = $booking->status === 'awaiting_deposit' && PlatformSetting::cliqConfigured();

        return view('booking.show', compact('booking', 'services', 'receiptUnderReview', 'cancellationRequiresReview', 'cliqConfigured'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        abort_unless($request->user()->role === 'customer', 403);
        $booking = $request->user()->bookings()->where('status', 'draft')->findOrFail($id);

        return view("booking.edit", compact("booking"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_unless($request->user()->role === 'customer', 403);

        $data = $request->validate($this->bookingRules());
        $data = $this->draftValues($data);

        DB::transaction(function () use ($request, $id, $data) {
            $booking = $request->user()->bookings()->where('status', 'draft')->lockForUpdate()->findOrFail($id);
            $booking->fill($data);
            if ($booking->isDirty([
                'vehicle_id', 'pickup_latitude', 'pickup_longitude',
                'destination_latitude', 'destination_longitude', 'scheduled_start_at', 'scheduled_end_at',
            ])) {
                $booking->fill([
                    'distance_km' => null, 'total_price' => null, 'deposit_amount' => null,
                    'commission_rate' => null, 'commission_amount' => null,
                ]);
            }
            $booking->save();
        });

        return redirect()->route('booking.show', $id)
            ->with('success', 'Booking updated successfully');
    }

    private function bookingRules(): array
    {
        return [
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'pickup_latitude' => ['required_with:pickup_longitude', 'nullable', 'numeric', 'decimal:0,7', 'between:-90,90'],
            'pickup_longitude' => ['required_with:pickup_latitude', 'nullable', 'numeric', 'decimal:0,7', 'between:-180,180'],
            'destination_latitude' => ['required_with:destination_longitude', 'nullable', 'numeric', 'decimal:0,7', 'between:-90,90'],
            'destination_longitude' => ['required_with:destination_latitude', 'nullable', 'numeric', 'decimal:0,7', 'between:-180,180'],
            'pickup_floor' => ['sometimes', 'required', 'integer', 'between:-32768,32767'],
            'pickup_has_elevator' => ['sometimes', 'required', 'boolean'],
            'destination_floor' => ['sometimes', 'required', 'integer', 'between:-32768,32767'],
            'destination_has_elevator' => ['sometimes', 'required', 'boolean'],
            'scheduled_start_at' => ['nullable', 'bail', 'date', function ($attribute, $value, $fail) {
                if (!\Illuminate\Support\Carbon::parse($value, config('booking.timezone'))->isFuture()) {
                    $fail('Choose a booking time in the future (Amman time).');
                }
            }],

            'notes' => ['nullable', 'string', 'max:16383'],
        ];
    }

    private function draftValues(array $data): array
    {
        if (array_key_exists('vehicle_id', $data)) {
            $data['driver_id'] = isset($data['vehicle_id']) ? Vehicle::findOrFail($data['vehicle_id'])->driver_id : null;
        }
        if (array_key_exists('scheduled_start_at', $data)) {
            $appointment = $data['scheduled_start_at'] === null
                ? null
                : Carbon::parse($data['scheduled_start_at'], config('booking.timezone'))->utc();

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
            $booking = $request->user()->bookings()->where('status', 'draft')->lockForUpdate()->findOrFail($id);
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
