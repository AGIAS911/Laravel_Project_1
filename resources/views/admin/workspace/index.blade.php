@extends('layouts.moveit-public')

@section('title', 'Admin dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('landing/css/admin-dashboard.css') }}">
    <script src="{{ asset('landing/js/admin-navigation.js') }}" defer></script>
@endpush

@section('page')
<div class="admin-shell">
    @include('layouts.admin-sidebar', ['adminPendingDrivers' => $metrics['drivers'], 'adminPendingReceipts' => $metrics['receipts']])

    <div class="admin-main">
        @include('layouts.admin-topbar')

        <main class="admin-content">
            <h1>Admin dashboard</h1>
            <p class="muted">An overview of operations.</p>

            <div class="admin-metrics">
                @foreach ([
                    [$metrics['bookings'], 'Total bookings', 'fa-clipboard-list'],
                    [$metrics['receipts'], 'Pending receipts', 'fa-money-check-dollar'],
                    [$metrics['drivers'], 'Drivers awaiting approval', 'fa-user-check'],
                    [number_format($metrics['commission'], 3).' JOD', 'Recorded commissions', 'fa-coins'],
                ] as [$value, $label, $icon])
                    <article class="admin-metric">
                        <span class="admin-metric-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        <div><strong>{{ $value }}</strong><span>{{ $label }}</span></div>
                    </article>
                @endforeach
            </div>

            <div class="admin-overview">
                <section class="admin-panel">
                    <h2>Bookings in the last 7 days</h2>
                    @php($peak = max(1, $activity->max('count')))
                    <ul class="admin-chart" aria-label="Daily bookings">
                        @foreach ($activity as $day)
                            <li aria-label="{{ $day['date'] }}: {{ $day['count'] }} bookings" title="{{ $day['date'] }}: {{ $day['count'] }} bookings">
                                <div class="admin-chart-track"><span @class(['is-peak' => $day['count'] === $peak]) style="height: {{ $day['count'] / $peak * 100 }}%"></span></div>
                                <span>{{ $day['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="admin-panel">
                    <h2>Needs action</h2>
                    <div class="admin-notices">
                        <a class="admin-warning" href="{{ route('admin.payments.index') }}"><i class="fa-solid fa-money-check-dollar" aria-hidden="true"></i><span><strong>{{ $metrics['receipts'] }} payment proofs</strong> pending review</span></a>
                        <a href="{{ route('admin.driver-approvals') }}"><i class="fa-solid fa-user-clock" aria-hidden="true"></i><span><strong>{{ $metrics['drivers'] }} drivers</strong> pending approval</span></a>
                        @if ($expiredWithProof)
                            <a href="{{ route('admin.payments.index') }}"><i class="fa-solid fa-hourglass-end" aria-hidden="true"></i><span>{{ $expiredWithProof }} overdue {{ str('booking')->plural($expiredWithProof) }} with payment proof under review</span></a>
                        @endif
                    </div>
                </section>
            </div>

            <section class="admin-panel">
                <div class="admin-panel-heading"><h2>Latest bookings</h2><a class="link" href="{{ route('booking.index') }}">All</a></div>
                <div class="admin-table">
                    <table>
                        <thead><tr><th scope="col">Booking</th><th scope="col">Customer</th><th scope="col">Driver</th><th scope="col">Amount</th><th scope="col">Status</th></tr></thead>
                        <tbody>
                            @forelse ($latestBookings as $booking)
                                <tr>
                                    <th scope="row"><a href="{{ route('booking.show', $booking) }}">{{ $booking->id }}</a></th>
                                    <td>{{ $booking->customer?->name ?? '—' }}</td>
                                    <td>{{ $booking->driver?->user?->name ?? '—' }}</td>
                                    <td>{{ $booking->total_price !== null ? $booking->total_price . ' JOD' : '—' }}</td>
                                    <td>
                                        <span class="admin-status">
                                            @if ($booking->status === 'draft')
                                                <i class="fa-regular fa-pen-to-square me-1 text-secondary-emphasis" aria-hidden="true"></i>
                                            @endif
                                            {{ str($booking->status)->headline() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No bookings yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>
@endsection
