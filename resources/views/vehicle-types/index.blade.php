@extends('vehicle-types.layout')

@section('title', 'Vehicle types')

@section('content')
    <header class="product-page-header">
        <div><h1 class="product-title">Vehicle types</h1><p class="text-body-secondary mt-1 mb-0">Name, description, photo and active status.</p></div>
        <a class="btn btn-primary product-action" href="{{ route('vehicle-types.create') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add vehicle type</a>
    </header>
    <div class="panel">
        <div class="table-wrap">
            <table class="vehicle-type-list" role="table" aria-label="Vehicle types">
                <thead role="rowgroup">
                    <tr role="row">
                        <th scope="col" role="columnheader">Vehicle</th><th scope="col" role="columnheader">Type</th>
                        <th scope="col" role="columnheader">Description</th><th scope="col" role="columnheader">Status</th>
                        <th scope="col" role="columnheader">Actions</th>
                    </tr>
                </thead>
                <tbody role="rowgroup">
                    @forelse ($vehicleTypes as $vehicleType)
                        <tr role="row">
                            <td class="vehicle-type-thumbnail" role="cell">
                                <x-vehicle-illustration :name="$vehicleType->name" :photo="$vehicleType->image" />
                            </td>
                            <th class="vehicle-type-name" scope="row" role="rowheader">{{ $vehicleType->name }}</th>
                            <td class="vehicle-type-description" role="cell">{{ $vehicleType->description ?: '—' }}</td>
                            <td class="vehicle-type-status" role="cell">@include('booking._status', ['state' => $vehicleType->is_active ? 'active' : 'inactive'])</td>
                            <td class="vehicle-type-actions" role="cell">
                                <div class="actions">
                                    <a class="btn btn-outline-secondary product-action" href="{{ route('vehicle-types.show', $vehicleType) }}" aria-label="View {{ $vehicleType->name }}"><i class="fa-solid fa-eye" aria-hidden="true"></i> View</a>
                                    <a class="btn btn-outline-secondary product-action" href="{{ route('vehicle-types.edit', $vehicleType) }}" aria-label="Edit {{ $vehicleType->name }}"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit</a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.vehicle-types.status', $vehicleType) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn {{ $vehicleType->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} product-action" type="submit"
                                            name="is_active" value="{{ $vehicleType->is_active ? 0 : 1 }}"
                                            aria-label="{{ $vehicleType->is_active ? 'Disable' : 'Enable' }} {{ $vehicleType->name }}">
                                            <i class="fa-solid {{ $vehicleType->is_active ? 'fa-ban' : 'fa-circle-check' }}" aria-hidden="true"></i>
                                            {{ $vehicleType->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                    @if ($vehicleType->vehicles_count === 0)
                                        <form method="POST" action="{{ route('vehicle-types.destroy', $vehicleType) }}" data-popup-title="Delete this vehicle type?" data-popup-action="Delete vehicle type" data-popup-danger>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger product-action" type="submit" aria-label="Delete {{ $vehicleType->name }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr role="row"><td class="vehicle-type-empty" colspan="5" role="cell">No vehicle types found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $vehicleTypes->links('pagination::bootstrap-5') }}
    </div>
@endsection
