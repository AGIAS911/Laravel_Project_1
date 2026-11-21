@extends('layouts.app')

@section('title', 'Driver dashboard | MoveIt')

@section('content')
    <div class="driver-dashboard">
        <header class="driver-intro">
            <div>
                <h1>Manage your moves</h1>
                <p>Welcome, {{ auth()->user()->name }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @php($pendingRequests = $counts['pending'] ?? 0)
                <a class="btn btn-primary" href="{{ route('driver.bookings.index', ['view' => 'requests']) }}"
                    aria-label="New requests: {{ $pendingRequests }}">
                    New requests
                    @if ($pendingRequests > 0)
                        <span class="badge text-bg-light text-primary ms-1" aria-hidden="true">{{ $pendingRequests }}</span>
                    @endif
                </a>
                <a class="btn btn-outline-light" href="{{ route('driver.schedule') }}">Schedule</a>
            <a class="driver-button driver-button-outline driver-profile-link" href="{{ route('driver.profile.show') }}">
                <i class="fa-regular fa-id-card" aria-hidden="true"></i> Driver profile
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if ($driver->status === 'approved')
            @php
                $next = $upcoming->first();
                $nextDate = $next?->scheduled_start_at?->timezone(config('booking.timezone'));
                $requestCount = (int) ($counts['pending'] ?? 0);
            @endphp

            <div class="driver-dispatch">
                <section class="driver-next" aria-labelledby="driver-next-title">
                    <header class="driver-next-header">
                        <h2 id="driver-next-title"><i class="fa-solid fa-route" aria-hidden="true"></i>{{ $next?->status === 'in_progress' ? 'Move in progress' : 'Next move' }}</h2>
                        @if ($next?->status === 'confirmed')
                            <span class="driver-confirmed"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Confirmed</span>
                        @endif
                    </header>
                    @if ($next)
                        <div class="driver-next-content">
                            <p class="driver-next-name">{{ $next->customer?->name ?? 'Booking #'.$next->id }}</p>
                            <p class="driver-next-meta">
                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                @if ($nextDate)
                                    <time datetime="{{ $nextDate->toIso8601String() }}">{{ $nextDate->format('D, d M · H:i') }}</time> <span>Amman</span>
                                @else
                                    Time to be confirmed
                                @endif
                            </p>
                            @if ($next->vehicle)
                                <p class="driver-next-meta"><i class="fa-solid fa-truck" aria-hidden="true"></i>{{ $next->vehicle->name }}</p>
                            @endif
                        </div>
                        <a class="driver-button driver-button-light" href="{{ route('booking.show', $next) }}">Open booking #{{ $next->id }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    @else
                        <div class="driver-next-content">
                            <p class="driver-next-name">No moves scheduled</p>
                            <p class="driver-next-meta">Your next confirmed booking will appear here.</p>
                        </div>
                        <a class="driver-button driver-button-light" href="{{ route('driver.schedule') }}">View schedule <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </section>

                <section class="driver-queue" aria-labelledby="driver-queue-title">
                    <div class="driver-queue-icon"><i class="fa-solid fa-inbox" aria-hidden="true"></i></div>
                    <h2 id="driver-queue-title">New requests</h2>
                    <p class="driver-queue-count">{{ $requestCount }} <span>{{ $requestCount === 1 ? 'request' : 'requests' }} to review</span></p>
                    <a class="driver-button driver-button-primary" href="{{ route('driver.bookings.index', ['view' => 'requests']) }}">Review requests <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </section>
            </div>

            <section class="driver-overview" aria-labelledby="driver-overview-title">
                <h2 id="driver-overview-title">Booking overview</h2>
                <dl class="driver-stats">
                    @foreach ([
                        ['confirmed', 'Confirmed', 'fa-calendar-check'],
                        ['in_progress', 'In progress', 'fa-route'],
                        ['completed', 'Completed', 'fa-circle-check'],
                    ] as [$status, $label, $icon])
                        <div class="driver-stat">
                            <span class="driver-stat-icon driver-stat-{{ $status }}" aria-hidden="true"><i class="fa-solid {{ $icon }}"></i></span>
                            <div>
                                <dt>{{ $label }}</dt>
                                <dd>{{ $counts[$status] ?? 0 }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </section>

            @if ($upcoming->count() > 1)
                <section class="driver-upcoming" aria-labelledby="driver-upcoming-title">
                    <header>
                        <h2 id="driver-upcoming-title">Also on your schedule</h2>
                        <a href="{{ route('driver.schedule') }}">View schedule <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </header>
                    <ul>
                        @foreach ($upcoming->skip(1) as $move)
                            @php($date = $move->scheduled_start_at?->timezone(config('booking.timezone')))
                            <li>
                                <a href="{{ route('booking.show', $move) }}">
                                    <span class="driver-stat-icon driver-stat-confirmed" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
                                    <span class="driver-upcoming-name"><strong>{{ $move->customer?->name ?? 'Booking #'.$move->id }}</strong><span>Booking #{{ $move->id }} · {{ str($move->status)->headline() }}</span></span>
                                    @if ($date)
                                        <time datetime="{{ $date->toIso8601String() }}">{{ $date->format('D, d M · H:i') }}</time>
                                    @endif
                                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @else
            <p class="alert alert-info">Your driver profile is under review. You can check your status in your profile.</p>
        @endif
    </div>
@endsection
