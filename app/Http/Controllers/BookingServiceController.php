<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bookingServices = $this->visibleServices($request)
            ->with(['booking', 'service'])
            ->latest('id')
            ->paginate(10);

        return view('booking_services.index', compact('bookingServices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $bookings = $this->draftBookings($request)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('booking_services.create', compact('bookings', 'services'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where('is_active', true),
                Rule::unique('booking_services')->where('booking_id', $request->input('booking_id')),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $booking = $this->draftBookings($request)->findOrFail($data['booking_id']);
        $service = Service::findOrFail($data['service_id']);

        // Fixed-price services are always a single unit.
        if ($service->unit === 'fixed') {
            $data['quantity'] = 1;
        }

        if ($service->type === Service::TYPE_TRANSPORT && $this->hasType($booking, Service::TYPE_TRANSPORT)) {
            $this->fail('service_id', __('booking_services.one_transport'));
        }

        if ($service->type === Service::TYPE_ASSEMBLY && ! $this->hasType($booking, Service::TYPE_WORKERS)) {
            $this->fail('service_id', __('booking_services.assembly_needs_workers'));
        }

        // Snapshot the service values so later price changes do not affect this booking.
        BookingService::create([
            'booking_id' => $booking->id,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'unit' => $service->unit,
            'quantity' => $data['quantity'],
            'unit_price' => $service->price,
        ]);

        return redirect()->route('booking-services.index')
            ->with('success', __('booking_services.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $bookingService = $this->visibleServices($request)->with(['booking', 'service'])->findOrFail($id);

        return view('booking_services.show', compact('bookingService'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $bookingService = $this->editableServices($request)->with(['booking', 'service'])->findOrFail($id);

        return view('booking_services.edit', compact('bookingService'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bookingService = $this->editableServices($request)->findOrFail($id);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        if ($bookingService->unit === 'fixed') {
            $data['quantity'] = 1;
        }

        $bookingService->update($data);

        return redirect()->route('booking-services.index')
            ->with('success', __('booking_services.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $bookingService = $this->editableServices($request)->with(['booking', 'service'])->findOrFail($id);

        $removesWorkers = $bookingService->service?->type === Service::TYPE_WORKERS;

        if ($removesWorkers && $this->hasType($bookingService->booking, Service::TYPE_ASSEMBLY)) {
            return back()->withErrors(['service' => __('booking_services.workers_needed_for_assembly')]);
        }

        $bookingService->delete();

        return redirect()->route('booking-services.index')
            ->with('success', __('booking_services.deleted'));
    }

    /**
     * Rows the user may see: admin all, driver own requests, customer own bookings.
     */
    private function visibleServices(Request $request)
    {
        $user = $request->user();
        $query = BookingService::query();

        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->role === 'driver') {
            return $query->whereHas('booking', fn ($q) => $q->where('driver_id', $user->driverProfile?->id));
        }

        return $query->whereHas('booking', fn ($q) => $q->where('customer_id', $user->id));
    }

    /**
     * Rows that may be changed: draft bookings only, own for customers, any for admin.
     */
    private function editableServices(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['admin', 'customer']), 403);

        return BookingService::whereHas('booking', function ($q) use ($user) {
            $q->where('status', 'draft');

            if ($user->role !== 'admin') {
                $q->where('customer_id', $user->id);
            }
        });
    }

    /**
     * Draft bookings the user may attach services to.
     */
    private function draftBookings(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['admin', 'customer']), 403);

        $query = Booking::where('status', 'draft')->orderByDesc('id');

        if ($user->role !== 'admin') {
            $query->where('customer_id', $user->id);
        }

        return $query;
    }

    private function hasType(Booking $booking, string $type): bool
    {
        return $booking->bookingServices()
            ->whereHas('service', fn ($q) => $q->where('type', $type))
            ->exists();
    }

    private function fail(string $field, string $message): void
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
