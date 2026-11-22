@extends('layouts.app')

@section('title', 'My vehicles | MoveIt')

@section('content')
    <section class="driver-fleet" aria-labelledby="fleet-title">
        <header class="driver-fleet-heading product-operator-heading">
            <div>
                <h1 id="fleet-title">My vehicles</h1>
                <p>You can own multiple vehicles.</p>
            </div>
            <a class="driver-button driver-fleet-add" href="{{ route('vehicles.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Add vehicle
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <div class="driver-fleet-table">
            @if ($vehicles->isNotEmpty())
                <table role="table" aria-label="My vehicles">
                    <thead role="rowgroup">
                        <tr role="row">
                            <th role="columnheader" scope="col"><span class="visually-hidden">Image</span></th>
                            <th role="columnheader" scope="col">Type</th>
                            <th role="columnheader" scope="col">License plate</th>
                            <th role="columnheader" scope="col">Capacity</th>
                            <th role="columnheader" scope="col">Base / km</th>
                            <th role="columnheader" scope="col">Status</th>
                            <th role="columnheader" scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody role="rowgroup">
                        @foreach ($vehicles as $vehicle)
                            @php
                                $type = $vehicle->vehicleType?->name;
                                $photo = $vehicle->image ?: $vehicle->vehicleType?->image;
                            @endphp
                            <tr role="row">
                                <td role="cell" class="driver-fleet-image">
                                    <x-vehicle-illustration :name="$type ?? $vehicle->name" :photo="$photo" class="driver-fleet-art" />
                                </td>
                                <td role="cell" class="driver-fleet-type">
                                    <a href="{{ route('vehicles.show', $vehicle) }}" @if ($type && $type !== $vehicle->name) aria-label="{{ $type }} · {{ $vehicle->name }}" @endif>{{ $type ?: $vehicle->name }}</a>
                                </td>
                                <td role="cell" class="driver-fleet-plate"><span class="driver-fleet-mobile-label">License plate</span><bdi>{{ $vehicle->plate_number }}</bdi></td>
                                <td role="cell" class="driver-fleet-capacity"><span class="driver-fleet-mobile-label">Capacity</span>{{ $vehicle->capacity_kg ? ucfirst($vehicle->capacity_kg) : '—' }}</td>
                                <td role="cell" class="driver-fleet-price"><span class="driver-fleet-mobile-label">Base / km</span>{{ $vehicle->base_price }} / {{ $vehicle->price_per_km }}</td>
                                <td role="cell" class="driver-fleet-state"><span @class(['driver-fleet-badge', 'is-'.$vehicle->status])>{{ str($vehicle->status)->headline() }}</span></td>
                                <td role="cell" class="driver-fleet-action">
                                    <a class="driver-button driver-fleet-edit product-action" href="{{ route('vehicles.edit', $vehicle) }}" aria-label="Edit {{ $vehicle->name }}"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="driver-fleet-empty">
                    <i class="fa-solid fa-truck" aria-hidden="true"></i>
                    <h2>{{ $search !== '' || $status ? 'No matching vehicles' : 'No vehicles yet' }}</h2>
                    @if ($search !== '' || $status)
                        <a href="{{ route('vehicles.index') }}">Clear filters</a>
                    @else
                        <p>Add your first vehicle to manage it here.</p>
                    @endif
                </div>
            @endif
        </div>

        @if ($vehicles->hasPages() || $vehicles->isNotEmpty() || $search !== '' || $status)
            <footer class="driver-fleet-footer">
                <details @if ($search !== '' || $status) open @endif>
                    <summary><i class="fa-solid fa-sliders" aria-hidden="true"></i> Filter vehicles</summary>
                    <form method="GET" action="{{ route('vehicles.index') }}" role="search">
                        <label class="visually-hidden" for="fleet-search">Vehicle name or license plate</label>
                        <input class="form-control" id="fleet-search" type="search" name="q" value="{{ $search }}" placeholder="Name or license plate">
                        <label class="visually-hidden" for="fleet-status">Vehicle status</label>
                        <select class="form-select" id="fleet-status" name="status">
                            <option value="">All statuses</option>
                            @foreach (['active', 'maintenance', 'inactive'] as $option)
                                <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                            @endforeach
                        </select>
                        <button class="driver-button driver-fleet-edit product-action" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Apply filters</button>
                    </form>
                </details>
                {{ $vehicles->links('pagination::bootstrap-5') }}
            </footer>
        @endif
    </section>
@endsection
