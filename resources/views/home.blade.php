@extends('layouts.dashboard')

@section('title', 'Dashboard | MoveIt')
@section('page-title', 'Welcome, ' . auth()->user()->name)

@section('content')
    @if (auth()->user()->role === 'customer')
        <section class="workspace-banner d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">Ready for a new move?</h2>
                <p class="mb-0">Start your inventory and we will suggest suitable vehicles.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('booking.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> New booking
            </a>
        </section>

        <div class="row g-3 mb-4">
            @foreach ([
                [array_sum($counts), 'Total bookings', 'fa-box', ''],
                [$counts['awaiting_deposit'] ?? 0, 'Awaiting deposit', 'fa-hourglass-half', 'deposit'],
                [$counts['confirmed'] ?? 0, 'Confirmed bookings', 'fa-circle-check', 'confirmed'],
                [$counts['draft'] ?? 0, 'Drafts', 'fa-pen-ruler', 'draft'],
            ] as [$count, $label, $icon, $tone])
                <div class="col-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="workspace-metric-icon {{ $tone }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                            <div>
                                <strong class="fs-3 d-block lh-1 mb-1 workspace-heading">{{ $count }}</strong>
                                <div class="text-body-secondary small">{{ $label }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <section class="card" aria-labelledby="recent-bookings-title">
                    <div class="card-header d-flex align-items-center justify-content-between gap-2">
                        <h2 class="h6 fw-bold mb-0" id="recent-bookings-title">Recent bookings</h2>
                        <a href="{{ route('booking.index') }}">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead><tr><th scope="col">Booking ID</th><th scope="col">Date and time</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                            <tbody>
                                @forelse ($recentBookings as $booking)
                                    <tr>
                                        <td><strong>#{{ $booking->id }}</strong></td>
                                        <td>{{ $booking->scheduled_start_at?->timezone('Asia/Amman')->format('d M Y, H:i') ?? '—' }}</td>
                                        <td>{{ str($booking->status)->headline() }}</td>
                                        <td><a class="btn btn-outline-secondary btn-sm" href="{{ route('booking.show', $booking) }}">Details</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">You have no bookings yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="col-lg-5">
                <section class="card" aria-labelledby="recent-notifications-title">
                    @if ($counts['awaiting_deposit'] ?? 0)
                        <div class="card-body">
                            <h2 class="h6 fw-bold">Action required</h2>
                            <p>Review the bookings awaiting a deposit and upload a receipt.</p>
                            <a class="btn btn-primary w-100" href="{{ route('payments.index') }}">View deposits</a>
                        </div>
                    @endif
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                        <h2 class="h6 fw-bold mb-0" id="recent-notifications-title">Recent notifications</h2>
                        <a href="{{ route('notifications.index') }}">View all</a>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse ($recentNotifications as $notification)
                            <li class="list-group-item">
                                <div @class(['fw-bold' => !$notification->read_at])>{{ $notification->title }}</div>
                                <time class="small text-body-secondary" datetime="{{ $notification->created_at->toISOString() }}">{{ $notification->created_at->diffForHumans() }}</time>
                            </li>
                        @empty
                            <li class="list-group-item text-body-secondary">No notifications yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    @else
        <a class="btn btn-primary" href="{{ route(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'driver.dashboard') }}">
            Open your dashboard
        </a>
    @endif
@endsection
