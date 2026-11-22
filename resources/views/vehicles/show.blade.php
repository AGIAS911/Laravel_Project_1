@extends('vehicle-types.layout')

@section('title', $vehicle->name)

@section('content')
    <section class="panel">
        <header class="product-page-header">
            <h1 class="product-title">{{ $vehicle->name }}</h1>
            @include('booking._status', ['state' => $vehicle->status])
        </header>
        <p>
            <x-vehicle-illustration :name="$vehicle->vehicleType?->name ?? $vehicle->name"
                :photo="$vehicle->image ?: $vehicle->vehicleType?->image" class="product-vehicle-preview" />
        </p>

        <p><strong>License Plate:</strong> {{ $vehicle->plate_number }}</p>
        <p><strong>Vehicle Type:</strong> {{ $vehicle->vehicleType?->name ?? '—' }}</p>
        <p><strong>Driver Name:</strong> {{ $vehicle->driver?->user?->name ?? '—' }}</p>
        <p><strong>Driver License:</strong> {{ $vehicle->driver?->license_number ?? '—' }}</p>
        <p><strong>Weight Capacity:</strong> {{ ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'][$vehicle->capacity_kg] ?? $vehicle->capacity_kg }}</p>
        <p><strong>Volume Capacity:</strong> {{ ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'][$vehicle->capacity_m3] ?? $vehicle->capacity_m3 }}</p>
        <p><strong>Base Price:</strong> {{ number_format((float) $vehicle->base_price, 3) }}</p>
        <p><strong>Price per Kilometer:</strong> {{ number_format((float) $vehicle->price_per_km, 3) }}</p>
        <p><strong>Status:</strong>
            @switch($vehicle->status)
                @case('active') Active @break
                @case('inactive') Inactive @break
                @case('maintenance') Maintenance @break
                @default {{ $vehicle->status }}
            @endswitch
        </p>

        <div class="actions">
            <a class="btn btn-primary product-action" href="{{ route('vehicles.edit', $vehicle) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit</a>
            <a class="btn btn-outline-secondary product-action" href="{{ route('vehicles.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to vehicles</a>
            @if ($vehicle->bookings->isEmpty())
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" data-popup-title="Delete this vehicle?" data-popup-action="Delete vehicle" data-popup-danger>
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger product-action" type="submit"><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Delete</button>
                </form>
            @else
                <span class="field-error">This vehicle cannot be deleted because it has bookings.</span>
            @endif
        </div>
    </section>
@endsection
