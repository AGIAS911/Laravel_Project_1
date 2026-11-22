@extends('vehicle-types.layout')

@section('title', 'Vehicles')

@section('content')
    <header class="product-page-header">
        <div><h1 class="product-title">Vehicles</h1><p class="text-body-secondary mt-1 mb-0">All driver vehicles.</p></div>
        <a class="btn btn-primary product-action" href="{{ route('vehicles.create') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add vehicle</a>
    </header>
    <section class="panel">
        <form method="GET" action="{{ route('vehicles.index') }}" class="admin-filterbar" role="search">
            <input type="search" name="q" value="{{ $search }}" aria-label="Search vehicles" placeholder="Search...">
            <select name="status" aria-label="Vehicle status">
                <option value="">All statuses</option>
                @foreach (['active', 'maintenance', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary product-action" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filter</button>
        </form>
        <div class="table-wrap">
            <table class="admin-fleet-list" aria-label="Driver vehicles">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>License plate</th>
                        <th>Capacity</th>
                        <th>Status</th>

                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td>
                                <div class="fleet-vehicle-name">
                                    <x-vehicle-illustration :name="$vehicle->vehicleType?->name ?? $vehicle->name"
                                        :photo="$vehicle->image ?: $vehicle->vehicleType?->image" />
                                    <span>{{ $vehicle->name }}</span>
                                </div>
                            </td>
                            <td>{{ $vehicle->driver?->user?->name ?? '—' }}</td>
                            <td>{{ $vehicle->plate_number }}</td>
                            <td>{{ is_numeric($vehicle->capacity_kg) ? number_format((float) $vehicle->capacity_kg) . ' kg' : ($vehicle->capacity_kg ? str($vehicle->capacity_kg)->headline() : '—') }}</td>
                            <td>@include('booking._status', ['state' => $vehicle->status, 'label' => ucfirst($vehicle->status)])</td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-outline-secondary product-action" href="{{ route('vehicles.show', $vehicle) }}" aria-label="View {{ $vehicle->name }}"><i class="fa-solid fa-eye" aria-hidden="true"></i> View</a>
                                    <a class="btn btn-outline-secondary product-action" href="{{ route('vehicles.edit', $vehicle) }}" aria-label="Edit {{ $vehicle->name }}"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit</a>
                                    @if ($vehicle->bookings_count === 0)
                                        <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" data-popup-title="Delete this vehicle?" data-popup-action="Delete vehicle" data-popup-danger>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger product-action" type="submit" aria-label="Delete {{ $vehicle->name }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Delete</button>
                                        </form>
                                    @else
                                        <span class="field-error">Linked to bookings</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No vehicles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $vehicles->links('pagination::bootstrap-5') }}
    </section>
@endsection
