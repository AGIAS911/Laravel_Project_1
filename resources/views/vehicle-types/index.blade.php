@extends('vehicle-types.layout')

@section('title', 'Vehicle Types')

@section('content')
    <div class="panel">
        <div class="actions" style="justify-content: space-between">
            <h1>Vehicle Types</h1>
            <a class="button" href="{{ route('vehicle-types.create') }}">Add Vehicle Type</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Vehicles</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicleTypes as $vehicleType)
                        <tr>
                            <td>{{ $vehicleType->name }}</td>
                            <td>{{ $vehicleType->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>{{ $vehicleType->vehicles_count }}</td>
                            <td>
                                @if ($vehicleType->image)
                                    <img class="thumbnail" src="{{ asset($vehicleType->image) }}" alt="Image of {{ $vehicleType->name }}">
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('vehicle-types.show', $vehicleType) }}">View</a>
                                    <a class="button" href="{{ route('vehicle-types.edit', $vehicleType) }}">Edit</a>
                                    @if ($vehicleType->vehicles_count === 0)
                                        <form method="POST" action="{{ route('vehicle-types.destroy', $vehicleType) }}" onsubmit="return confirm('Delete this vehicle type?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button danger" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No vehicle types found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $vehicleTypes->links() }}
    </div>
@endsection
