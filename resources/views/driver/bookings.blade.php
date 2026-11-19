@extends('layouts.dashboard')

@section('title', 'Driver bookings')
@section('page-title', request('view') === 'requests' ? 'New requests' : 'Driver bookings')

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr><th>Booking</th><th>Customer</th><th>Vehicle</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>#{{ $booking->id }}</td>
                            <td>{{ $booking->customer->name }}</td>
                            <td>{{ $booking->vehicle?->name ?? '—' }}</td>
                            <td>{{ str($booking->status)->replace('_', ' ')->headline() }}</td>
                            <td><a class="btn btn-outline-secondary btn-sm" href="{{ route('booking.show', $booking) }}">Details</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No bookings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bookings->hasPages())
            <div class="card-body">{{ $bookings->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
