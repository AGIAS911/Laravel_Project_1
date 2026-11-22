@extends('layouts.dashboard')

@section('page-title', 'Driver schedule')
@section('page-description', 'View scheduled moves in Amman time.')
@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('driver.bookings.index') }}">All driver bookings</a>
@endsection

@section('content')
    @if ($bookings->isNotEmpty())
        <ol class="product-schedule list-unstyled" aria-label="Upcoming moves">
            @foreach ($bookings as $booking)
                @php($appointment = $booking->scheduled_start_at?->timezone(config('booking.timezone')))
                <li>
                    <div class="product-schedule-time">
                        @if ($appointment)
                            <time datetime="{{ $appointment->toIso8601String() }}">
                                <strong>{{ $appointment->format('H:i') }}</strong>{{ $appointment->format('D, d M') }}
                            </time>
                        @else
                            <span>Time to be set</span>
                        @endif
                    </div>
                    <div>
                        <h2 class="h6 mb-2">Booking {{ $booking->id }}</h2>
                        @include('booking._status', ['state' => $booking->status])
                    </div>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('booking.show', $booking) }}">Open booking</a>
                </li>
            @endforeach
        </ol>
        {{ $bookings->links('pagination::bootstrap-5') }}
    @else
        <section class="product-task">
            <div><h2 class="h5 mb-1">No upcoming moves.</h2><p class="mb-0">Your scheduled moves will appear here.</p></div>
            <a class="btn btn-outline-secondary" href="{{ route('driver.bookings.index', ['view' => 'requests']) }}">Review requests</a>
        </section>
    @endif
@endsection
