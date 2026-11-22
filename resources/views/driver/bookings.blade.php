@extends('layouts.dashboard')

@section('title', 'Driver bookings')
@section('page-title', request('view') === 'requests' ? 'New requests' : 'Driver bookings')

@section('page-description', 'Review requests from customers and track assigned moves.')

@section('content')
    <nav class="product-filterbar mb-3" aria-label="Driver bookings">
        <a @class(['move-filter', 'is-current' => request('view') !== 'requests']) href="{{ route('driver.bookings.index') }}" @if (request('view') !== 'requests') aria-current="page" @endif>All bookings</a>
        <a @class(['move-filter', 'is-current' => request('view') === 'requests']) href="{{ route('driver.bookings.index', ['view' => 'requests']) }}" @if (request('view') === 'requests') aria-current="page" @endif>New requests</a>
    </nav>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr><th>Booking</th><th>Customer</th><th>Vehicle</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td>{{ $booking->customer->name }}</td>
                            <td>{{ $booking->vehicle?->name ?? '—' }}</td>
                            <td>@include('booking._status', ['state' => $booking->status])</td>
                            <td><a class="btn btn-outline-secondary btn-sm" href="{{ route('booking.show', $booking) }}">Details</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">{{ request('view') === 'requests' ? 'No requests to review.' : 'No bookings yet.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bookings->hasPages())
            <div class="card-body">{{ $bookings->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
