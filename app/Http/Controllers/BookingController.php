<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Booking;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $bookings = Booking::latest('id')->paginate(10);
        } else {
            $bookings = $user->bookings()
                ->latest('id')
                ->paginate(10);
        }

        return view('booking.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("booking.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate($this->bookingRules());
        $data['customer_id'] = $request->user()->id;
        $data['status'] = 'draft';


        $bookings = Booking::create($data);

        return redirect()->route('booking.index')->with('success', 'Booking created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $booking = $user->role === 'admin'
            ? Booking::findOrFail($id)
            : $user->bookings()->findOrFail($id);

        return view('booking.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $booking = Booking::findOrFail($id);

        return view("booking.edit", compact("booking"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $booking = $request->user()
            ->bookings()
            ->where('status', 'draft')
            ->findOrFail($id);

        $data = $request->validate($this->bookingRules());

        $booking->update($data);

        return redirect()->route('booking.index')
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
            'scheduled_start_at' => ['required_with:scheduled_end_at', 'nullable', 'date'],
            'scheduled_end_at' => ['nullable', 'date', 'after:scheduled_start_at'],
            'notes' => ['nullable', 'string', 'max:16383'],
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        return redirect()->route('booking.index')
            ->with('success', 'Booking deleted successfully');    }
}
