@extends('layouts.dashboard')

@section('title', 'Edit booking ' . $booking->id . ' | MoveIt')
@section('page-title', 'Edit booking ' . $booking->id)
@section('page-icon', 'fa-route')
@section('page-description', 'Step 2: Locations and schedule.')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('booking.show', $booking) }}">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> Back to booking
    </a>
@endsection

@section('content')
    @include('booking._steps', [
        'current' => 'locations',
        'line' => true,
        'links' => ['inventory' => route('booking.show', $booking).'#inventory', 'vehicle' => '#vehicle-selection'],
    ])
    @include('booking._draft-form', ['draft' => $booking])
@endsection
