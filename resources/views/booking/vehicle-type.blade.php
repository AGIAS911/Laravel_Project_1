@extends('layouts.dashboard')

@php
    $heading = $booking->needsInventory() ? 'Recommended vehicle sizes' : 'Choose your vehicle size';
@endphp

@section('title', $heading . ' | MoveIt')
@section('page-title', $heading)
@section('page-description', 'll’ll pick an available vehicle after setting the move date and time.')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('booking.show', $booking) }}{{ $booking->needsInventory() ? '#inventory' : '' }}">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> {{ $booking->needsInventory() ? 'Back to inventory' : 'Back to draft' }}
    </a>
@endsection

@section('content')
    @include('booking._wizard-steps', ['current' => 'type'])

    @if ($booking->needsInventory())
        <div class="booking-size-note">
            <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
            <div>
                <strong>Estimated load</strong>
                <p>{{ number_format($estimate['weight'], 1) }} kg · {{ number_format($estimate['volume'], 2) }} m³</p>
                <p>{{ $recommendedTypes ? 'Recommended types have at least one active vehicle that fits these estimates.' : 'No available vehicle type matches this estimate. Review your inventory or contact support if you need a larger vehicle.' }}</p>
                <a href="{{ route('booking.show', $booking) }}#inventory">Edit inventory estimates</a>
            </div>
        </div>
    @endif

    @if ($vehicleTypes->isEmpty())
        <p class="alert alert-info">No vehicle types are active right now. <a href="{{ route('info.contact') }}">Contact support</a> for help.</p>
    @else
        <form method="POST" action="{{ route('booking.update', $booking) }}">
            @csrf @method('PUT')
            <input type="hidden" name="booking_step" value="type">
            @error('preferred_vehicle_type_id')<p class="alert alert-danger" role="alert">{{ $message }}</p>@enderror
            <fieldset>
                <legend class="visually-hidden">Vehicle types</legend>
                <div class="booking-type-grid">
                    @foreach ($vehicleTypes as $vehicleType)
                        @php($sizes = $vehicleType->vehicles->pluck('capacity_m3')->unique()->map(fn ($size) => ucfirst($size))->join(', '))
                        <label class="booking-type-option">
                            <input class="visually-hidden" type="radio" name="preferred_vehicle_type_id" value="{{ $vehicleType->id }}" required
                                @checked((string) old('preferred_vehicle_type_id', $booking->preferred_vehicle_type_id) === (string) $vehicleType->id)>
                            <span>
                                <img src="{{ asset($vehicleType->image ?: 'landing/img/driver-vehicle-truck.svg') }}" alt="" width="116" height="64">
                                <strong>{{ $vehicleType->name }}</strong>
                                @if ($sizes)<span>{{ $sizes }} capacity</span>@endif
                                @if ($vehicleType->description)<span>{{ $vehicleType->description }}</span>@endif
                                @if (in_array($vehicleType->id, $recommendedTypes, true))<span class="inventory-pill is-confirmed">Recommended</span>@endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <button class="btn btn-primary mt-3" type="submit">Continue to locations <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></button>
        </form>
    @endif
@endsection
