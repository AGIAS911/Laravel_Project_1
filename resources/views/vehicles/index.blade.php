@extends('vehicle-types.layout')

@section('title', 'Vehicles')

@section('content')
    <section class="panel">
        <div class="actions" style="justify-content: space-between">
            <h1>Vehicles</h1>
            <a class="button" href="{{ route('vehicles.create') }}">Add Vehicle</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>License Plate</th>
                        <th>Type</th>
                        <th>Driver License</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->name }}</td>
                            <td>{{ $vehicle->plate_number }}</td>
                            <td>{{ $vehicle->vehicleType?->name ?? '—' }}</td>
                            <td>{{ $vehicle->driver?->license_number ?? '—' }}</td>
                            <td>
                                @switch($vehicle->status)
                                    @case('active') Active @break
                                    @case('inactive') Inactive @break
                                    @case('maintenance') Maintenance @break
                                    @default {{ $vehicle->status }}
                                @endswitch
                            </td>
                            <td>
                                @if ($vehicle->image)
                                    <img class="thumbnail" src="{{ asset($vehicle->image) }}" alt="Image of {{ $vehicle->name }}">
                                @else
                                    —
                                @endif
                            </td>
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
                        <tr><td colspan="7">No vehicles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $vehicles->links() }}
    </section>
@endsection
