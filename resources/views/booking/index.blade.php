@extends('layouts.dashboard')

@section('title', 'Booking test')
@section('page-title', 'Booking test')

@section('content')
    <section class="border rounded bg-white p-3 p-md-4 mb-4" aria-labelledby="booking-form-title">
        <h2 id="booking-form-title" class="h5">Create a draft</h2>
        <p class="text-body-secondary">Vehicle and schedule are optional. Enter both coordinates for each location you provide.</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <p class="mb-1">Check the following fields:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('booking.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label" for="vehicle_id">Vehicle ID</label>
                    <input class="form-control" type="text" inputmode="numeric" id="vehicle_id" name="vehicle_id" value="{{ old('vehicle_id') }}">
                </div>

                @foreach (['pickup' => 'Pickup', 'destination' => 'Destination'] as $key => $label)
                    <fieldset class="col-12">
                        <legend class="fs-6 fw-semibold">{{ $label }}</legend>
                        <div class="row g-2">
                            @foreach (['latitude' => 'Latitude', 'longitude' => 'Longitude', 'floor' => 'Floor'] as $field => $fieldLabel)
                                @php($name = $key . '_' . $field)
                                <div class="col-sm">
                                    <label class="form-label" for="{{ $name }}">{{ $fieldLabel }}</label>
                                    <input class="form-control" inputmode="{{ $field === 'floor' ? 'numeric' : 'decimal' }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $field === 'floor' ? 0 : '') }}">
                                </div>
                            @endforeach
                            @php($name = $key . '_has_elevator')
                            <div class="col-sm">
                                <label class="form-label" for="{{ $name }}">Elevator</label>
                                <select class="form-select" id="{{ $name }}" name="{{ $name }}">
                                    <option value="0" @selected(!old($name))>No</option>
                                    <option value="1" @selected(old($name))>Yes</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                @endforeach

                @foreach (['scheduled_start_at' => 'Scheduled start (UTC)', 'scheduled_end_at' => 'Scheduled end (UTC)'] as $name => $label)
                    <div class="col-sm-6">
                        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
                        <input class="form-control" type="datetime-local" step="1" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}">
                    </div>
                @endforeach

                <div class="col-12">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary" type="submit">Create draft</button>
            </div>
        </form>
    </section>

    <section aria-labelledby="booking-list-title">
        <h2 id="booking-list-title" class="h5">{{ auth()->user()->role === 'admin' ? 'All bookings' : 'Your bookings' }}</h2>
        @forelse ($bookings as $booking)
            <div class="border-bottom py-2 d-flex align-items-center justify-content-between gap-2">
                <span>#{{ $booking->id }} · {{ ucfirst($booking->status) }}</span>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('booking.show', $booking->id) }}" aria-label="Show booking #{{ $booking->id }}">Show</a>
            </div>
        @empty
            <p>No bookings yet.</p>
        @endforelse
        <div class="mt-3">{{ $bookings->links('pagination::bootstrap-5') }}</div>
    </section>
@endsection
