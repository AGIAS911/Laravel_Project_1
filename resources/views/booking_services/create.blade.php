@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Add service to booking</h1>
        <a class="btn btn-outline-secondary" href="{{ route('booking-services.index') }}">Back</a>
    </div>

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

    <form method="POST" action="{{ route('booking-services.store') }}"
        class="border rounded bg-white p-3 p-md-4">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="booking_id">Booking</label>
                <select class="form-select @error('booking_id') is-invalid @enderror" id="booking_id"
                    name="booking_id" required>
                    <option value="">Select a booking</option>
                    @foreach ($bookings as $booking)
                        <option value="{{ $booking->id }}" @selected(old('booking_id') == $booking->id)>
                            #{{ $booking->id }} · {{ ucfirst($booking->status) }}
                        </option>
                    @endforeach
                </select>
                @error('booking_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="service_id">Service</label>
                <select class="form-select @error('service_id') is-invalid @enderror" id="service_id"
                    name="service_id" required>
                    <option value="">Select a service</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                            {{ $service->name }} ({{ $service->price }} / {{ $service->unit }})
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="quantity">Quantity</label>
                <input class="form-control @error('quantity') is-invalid @enderror" type="number" step="0.01"
                    min="0.01" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required>
                @error('quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </form>
</div>
@endsection
