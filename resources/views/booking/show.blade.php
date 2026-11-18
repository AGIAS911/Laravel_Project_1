@extends('layouts.dashboard')

@section('title', 'Booking #' . $booking->id)
@section('page-title', 'Booking #' . $booking->id)

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('booking.index') }}">Back to bookings</a>
@endsection

@section('content')
    <dl class="row border rounded bg-white p-3 p-md-4">
        @foreach ([
            'Status' => ucfirst($booking->status),
            'Vehicle ID' => $booking->vehicle_id,
            'Pickup latitude' => $booking->pickup_latitude,
            'Pickup longitude' => $booking->pickup_longitude,
            'Pickup floor' => $booking->pickup_floor,
            'Pickup elevator' => $booking->pickup_has_elevator ? 'Yes' : 'No',
            'Destination latitude' => $booking->destination_latitude,
            'Destination longitude' => $booking->destination_longitude,
            'Destination floor' => $booking->destination_floor,
            'Destination elevator' => $booking->destination_has_elevator ? 'Yes' : 'No',
            'Scheduled start (UTC)' => $booking->scheduled_start_at?->utc()->format('Y-m-d H:i'),
            'Scheduled end (UTC)' => $booking->scheduled_end_at?->utc()->format('Y-m-d H:i'),
            'Notes' => $booking->notes,
        ] as $label => $value)
            <dt class="col-sm-5">{{ $label }}</dt>
            <dd class="col-sm-7 text-break">{{ $value ?? '—' }}</dd>
        @endforeach
    </dl>
@endsection
