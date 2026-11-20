@extends('layouts.dashboard')
@section('page-title', 'Driver schedule')

@section('content')
    <section class="card card-body">
        <h2 class="h5">Upcoming moves · Amman time</h2>
        @forelse ($bookings as $booking)
            <div class="border-bottom py-2">
                <a href="{{ route('booking.show', $booking) }}">Booking {{ $booking->id }}</a>
                <p class="mb-0">{{ $booking->scheduled_start_at->timezone('Asia/Amman')->format('D, d M · H:i') }}

                    · {{ str($booking->status)->headline() }}</p>
            </div>
        @empty
            <p>No upcoming moves.</p>
        @endforelse
        {{ $bookings->links('pagination::bootstrap-5') }}
    </section>
@endsection
