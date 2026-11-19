@extends('vehicle-types.layout')

@section('title', 'Vehicles')

@section('content')
    <section class="panel">
        <div class="actions" style="justify-content: space-between">
            <h1>Vehicles</h1>
            <a class="button" href="{{ route('vehicles.create') }}">Add Vehicle</a>
        </div>

        <p class="text-body-secondary">All driver vehicles.</p>
        <form method="GET" action="{{ route('vehicles.index') }}" class="admin-filterbar" role="search">
            <input type="search" name="q" value="{{ $search }}" aria-label="Search vehicles" placeholder="Search...">
            <select name="status" aria-label="Vehicle status">
                <option value="">All statuses</option>
                @foreach (['active', 'maintenance', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </form>
        <div class="table-wrap">
            <table>
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
                            <td>{{ $vehicle->name }}</td>
                            <td>{{ $vehicle->driver?->user?->name ?? '—' }}</td>
                            <td>{{ $vehicle->plate_number }}</td>
                            <td>{{ $vehicle->capacity_kg === null ? '—' : number_format((float) $vehicle->capacity_kg) . ' kg' }}</td>
                            <td>@include('booking._status', ['state' => $vehicle->status, 'label' => ucfirst($vehicle->status)])</td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('vehicles.show', $vehicle) }}">View</a>
                                    <a class="button" href="{{ route('vehicles.edit', $vehicle) }}">Edit</a>
                                    @if ($vehicle->bookings_count === 0)
                                        <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Delete this vehicle?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button danger" type="submit">Delete</button>
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
