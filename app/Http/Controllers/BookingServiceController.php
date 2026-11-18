<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = BookingService::with(['booking', 'service'])->latest('id');

        if ($user->role !== 'admin') {
            $query->whereHas('booking', fn ($q) => $q->where('customer_id', $user->id));
        }

        $bookingServices = $query->paginate(10);

        return view('booking_services.index', compact('bookingServices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $bookings = $this->availableBookings($request);
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
                'exists:services,id',
                Rule::unique('booking_services')->where('booking_id', $request->input('booking_id')),
            ],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $booking = $this->availableBookings($request)->firstWhere('id', $data['booking_id']);
        abort_if(! $booking, 403);

        $service = Service::findOrFail($data['service_id']);

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
            ->with('success', 'Booking service created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $bookingService = $this->findForUser($request, $id);

        return view('booking_services.show', compact('bookingService'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $bookingService = $this->findForUser($request, $id, true);

        return view('booking_services.edit', compact('bookingService'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bookingService = $this->findForUser($request, $id, true);

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $bookingService->update($data);

        return redirect()->route('booking-services.index')
            ->with('success', 'Booking service updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $bookingService = $this->findForUser($request, $id, true);
        $bookingService->delete();

        return redirect()->route('booking-services.index')
            ->with('success', 'Booking service deleted successfully');
    }

    /**
     * Bookings the current user may attach services to (admins: any, customers: own drafts).
     */
    private function availableBookings(Request $request)
    {
        $user = $request->user();

        $query = Booking::orderByDesc('id');

        if ($user->role !== 'admin') {
            $query->where('customer_id', $user->id)->where('status', 'draft');
        }

        return $query->get();
    }

    /**
     * Find a row the user may access; $editable also requires a draft booking for customers.
     */
    private function findForUser(Request $request, string $id, bool $editable = false): BookingService
    {
        $user = $request->user();

        $query = BookingService::with(['booking', 'service']);

        if ($user->role !== 'admin') {
            $query->whereHas('booking', function ($q) use ($user, $editable) {
                $q->where('customer_id', $user->id);

                if ($editable) {
                    $q->where('status', 'draft');
                }
            });
        }

        return $query->findOrFail($id);
    }
}
