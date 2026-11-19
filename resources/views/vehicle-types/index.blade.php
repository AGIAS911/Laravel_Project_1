@extends('vehicle-types.layout')

@section('title', 'Vehicle types')

@section('content')
    <div class="panel">
        <div class="actions" style="justify-content: space-between">
            <h1>Vehicle types</h1>
            <a class="button" href="{{ route('vehicle-types.create') }}">Add Vehicle Type</a>
        </div>

        <p class="text-body-secondary">Name, description, photo and active status.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Photo</th><th scope="col">Type</th>
                        <th scope="col">Description</th><th scope="col">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicleTypes as $vehicleType)
                        <tr>
                            <td>
                                @if ($vehicleType->image)
                                    <img class="thumbnail" src="{{ asset($vehicleType->image) }}" alt="Image of {{ $vehicleType->name }}">
                                @else
                                    <i class="fa-solid fa-truck fa-2x" aria-label="Vehicle type"></i>
                                @endif
                            </td>
                            <th scope="row">{{ $vehicleType->name }}</th>
                            <td>{{ $vehicleType->description ?: '—' }}</td>
                            <td>@include('booking._status', ['state' => $vehicleType->is_active ? 'active' : 'inactive'])</td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('vehicle-types.show', $vehicleType) }}">View</a>
                                    <a class="button" href="{{ route('vehicle-types.edit', $vehicleType) }}">Edit</a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.vehicle-types.status', $vehicleType) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-outline-danger btn-sm" name="is_active" value="{{ $vehicleType->is_active ? 0 : 1 }}">{{ $vehicleType->is_active ? 'Disable' : 'Enable' }}</button>
                                    </form>
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

        {{ $vehicleTypes->links('pagination::bootstrap-5') }}
    </div>
@endsection
