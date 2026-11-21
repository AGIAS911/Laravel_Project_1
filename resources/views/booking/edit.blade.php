@extends('layouts.dashboard')

@php
    $heading = match ($step) {
        'route' => 'Pickup and destination',
        'time' => 'Date and time',

        default => 'Edit booking ' . $booking->id,
    };
    $previous = ['route' => 'type', 'time' => 'route'][$step] ?? null;
@endphp

@section('title', $heading . ' | MoveIt')
@section('page-title', $heading)
@section('page-icon', 'fa-route')
@section('actions')
    <a class="btn btn-outline-secondary" href="{{ $previous ? route('booking.edit', ['booking' => $booking, 'step' => $previous]) : route('booking.show', $booking) }}">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> {{ $previous ? 'Previous step' : 'Back to booking' }}
    </a>
@endsection

@section('content')
    @if ($step)
        @include('booking._wizard-steps', ['current' => $step])
    @else
        @include('booking._steps', ['current' => 'locations', 'line' => true, 'links' => [
            'inventory' => route('booking.show', $booking).'#inventory', 'review' => route('booking.show', $booking).'#services',
        ]])
    @endif
    @include('booking._draft-form', ['draft' => $booking])
@endsection
