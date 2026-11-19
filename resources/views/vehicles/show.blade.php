@extends('vehicle-types.layout')

@section('title', $vehicle->name)

@section('content')
    <section class="panel">
        <h1>{{ $vehicle->name }}</h1>
        @if ($vehicle->image)
            <p><img src="{{ asset($vehicle->image) }}" alt="Image of {{ $vehicle->name }}" style="max-width: 420px; max-height: 300px"></p>
        @endif

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
            <a class="button" href="{{ route('vehicles.edit', $vehicle) }}">Edit</a>
            <a class="button secondary" href="{{ route('vehicles.index') }}">Back to Vehicles</a>
            @if ($vehicle->bookings->isEmpty())
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Delete this vehicle?')">
                    @csrf
                    @method('DELETE')
                    <button class="button danger" type="submit">Delete</button>
                </form>
            @else
                <span class="field-error">This vehicle cannot be deleted because it has bookings.</span>
            @endif
        </div>
    </section>
@endsection
