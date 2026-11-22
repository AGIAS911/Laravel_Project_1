@extends('layouts.dashboard')

@php
    $heading = $booking->needsInventory() ? 'Recommended vehicle sizes' : 'Choose your vehicle size';
@endphp

@section('title', $heading . ' | MoveIt')
@section('page-title', $heading)
@section('page-description', 'Choose the size for your move. We match a driver and vehicle after your deposit is verified.')

@section('actions')
    <a class="btn btn-outline-secondary product-action" href="{{ route('booking.show', $booking) }}{{ $booking->needsInventory() ? '#inventory' : '' }}">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ $booking->needsInventory() ? 'Back to inventory' : 'Back to draft' }}
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
                <legend class="visually-hidden">Select a vehicle size</legend>
                <div class="booking-type-grid">
                    @foreach ($vehicleTypes as $vehicleType)
                        @php
                            $sizes = $vehicleType->vehicles->pluck('capacity_m3')->filter()->unique()->map(fn ($size) => ucfirst($size))->join(', ');
                            $recommended = in_array($vehicleType->id, $recommendedTypes, true);
                            $optionId = 'booking-vehicle-type-'.$vehicleType->id;
                        @endphp
                        <label class="booking-type-option">
                            <input type="radio" name="preferred_vehicle_type_id" value="{{ $vehicleType->id }}" required
                                aria-labelledby="{{ $optionId }}-name" aria-describedby="{{ $optionId }}-details"
                                @checked((string) old('preferred_vehicle_type_id', $booking->preferred_vehicle_type_id) === (string) $vehicleType->id)>
                            <span>
                                <x-vehicle-illustration :name="$vehicleType->name" :photo="$vehicleType->image" class="booking-type-art" />
                                <strong id="{{ $optionId }}-name">{{ $vehicleType->name }}</strong>
                                <span class="booking-type-details" id="{{ $optionId }}-details">
                                    @if ($sizes)<span>Capacity: {{ $sizes }}</span>@endif
                                    @if ($vehicleType->description)<span>{{ $vehicleType->description }}</span>@endif
                                    @if ($recommended)
                                        <span class="inventory-pill is-confirmed"><i class="fa-solid fa-star" aria-hidden="true"></i> Recommended</span>
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <button class="btn btn-primary product-action mt-3" type="submit">Continue to locations <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </form>
    @endif
@endsection
