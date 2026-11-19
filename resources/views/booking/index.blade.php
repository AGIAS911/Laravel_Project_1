@extends('layouts.dashboard')

@php
    $customer = auth()->user()->role === 'customer';
    $admin = auth()->user()->role === 'admin';
@endphp

@section('title', $customer ? 'My bookings' : ($admin ? 'All bookings' : 'Bookings'))
@section('page-title', $customer ? 'My bookings' : ($admin ? 'All bookings' : 'Bookings'))
@section('page-icon', 'fa-box')

@section('actions')
    @if ($customer)
        <a class="btn btn-primary" href="{{ route('booking.create') }}">
            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> New booking
        </a>
    @endif
@endsection

@section('content')
    @if ($admin)
        <p class="text-body-secondary">Full booking overview.</p>
        <form action="{{ route('booking.index') }}" method="GET" class="admin-filterbar" role="search">
            <input name="q" type="search" value="{{ $search }}" aria-label="Booking ID" placeholder="Search by booking ID">
            <select name="status" aria-label="Booking status">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="driver" aria-label="Driver">
                <option value="">All drivers</option>
                @foreach ($drivers as $driver)
                    <option value="{{ $driver->id }}" @selected((string) $driver->id === $driverFilter)>{{ $driver->user?->name ?? 'Driver #' . $driver->id }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </form>
    @else
    <p class="text-body-secondary">Continue a draft or check the status of a move.</p>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <nav class="d-flex flex-wrap gap-2" aria-label="Booking status">
            @foreach (['' => 'All'] + $statuses as $value => $label)
                <a @class(['move-filter', 'is-current' => ($status ?? '') === $value])
                    href="{{ route('booking.index', array_filter(['status' => $value, 'q' => $search])) }}"
                    @if (($status ?? '') === $value) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <form action="{{ route('booking.index') }}" method="GET" class="d-flex gap-2" role="search">
            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input class="form-control" name="q" type="search" inputmode="numeric" maxlength="40" value="{{ $search }}"
                aria-label="Booking ID" placeholder="Search by booking ID">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </form>
    </div>

    @endif

    <div class="card move-table">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Booking ID</th>
                        @if ($admin)
                            <th scope="col">Customer</th>
                            <th scope="col">Driver</th>
                        @endif
                        <th scope="col">Date and time</th>
                        @unless ($admin)<th scope="col">Vehicle</th>@endunless
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <th scope="row">#{{ $booking->id }}</th>
                            @if ($admin)
                                <td>{{ $booking->customer?->name ?? '—' }}</td>
                                <td>{{ $booking->driver?->user?->name ?? '—' }}</td>
                            @endif
                            <td>{{ $booking->scheduled_start_at?->timezone('Asia/Amman')->format('d M Y · H:i') ?? '—' }}</td>
                            @unless ($admin)
                                <td>{{ $booking->vehicle?->vehicleType?->name ?? $booking->vehicle?->name ?? '—' }}</td>
                            @endunless
                            <td class="text-nowrap">{{ $booking->total_price !== null ? $booking->total_price . ' JOD' : '—' }}</td>
                            <td>@include('booking._status', ['state' => $booking->status])</td>
                            <td class="text-end">
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('booking.show', $booking) }}">
                                    {{ $customer && $booking->status === 'draft' ? 'Continue' : 'Details' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $admin ? 7 : 6 }}" class="text-center text-body-secondary py-4">No bookings match this view.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($bookings->hasPages())
        <div class="mt-3">{{ $bookings->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection
