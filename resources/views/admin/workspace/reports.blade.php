@extends('layouts.dashboard')

@section('title', 'Commission reports | MoveIt')
@section('page-title', 'Commission reports')
@section('page-description', 'Review locked commissions on completed bookings.')

@section('content')
    <section aria-label="All-time totals" class="mb-4">
        <p class="report-caption">All-time totals</p>
        <div class="admin-metrics admin-report-metrics">
            @foreach ([
                ['Recorded commission', number_format($recordedCommission, 3) . ' JOD', 'fa-coins'],
                ['Completed bookings', number_format($groups->firstWhere('status', 'completed')?->total ?? 0), 'fa-clipboard-check'],
                ['Approved deposits', number_format($approvedDeposits, 3) . ' JOD', 'fa-money-check-dollar'],
            ] as [$label, $value, $icon])
                <article class="admin-metric report-metric">
                    <span class="admin-metric-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
                </article>
            @endforeach
        </div>
        <p class="small text-body-secondary mt-2 mb-0">Recorded commission represents amounts locked on completed bookings. </p>
    </section>

    <section class="payment-queue" aria-labelledby="report-bookings">
        <div class="payment-queue-toolbar">
            <div>
                <h2 class="h5 workspace-heading mb-0" id="report-bookings">Completed bookings</h2>
                <p class="small text-body-secondary mb-0 mt-1">
                    {{ $completedBookings->total() }} {{ str('booking')->plural($completedBookings->total()) }}
                    @if ($search !== '')
                        matching <strong>“{{ $search }}”</strong>
                        <a class="ms-1" href="{{ route('admin.reports') }}">Clear search</a>
                    @endif
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form class="payment-search" method="GET" action="{{ route('admin.reports') }}" role="search">
                    <input class="form-control" type="search" name="q" value="{{ $search }}" inputmode="numeric"
                        maxlength="24" aria-label="Booking ID" placeholder="Search by booking ID">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </form>
                @if ($completedBookings->total())
                    <a class="btn btn-outline-secondary" href="{{ route('admin.reports.export', $search === '' ? [] : ['q' => $search]) }}">
                        <i class="fa-solid fa-download me-1" aria-hidden="true"></i> Export CSV
                    </a>
                @endif
            </div>
        </div>

        @if ($completedBookings->isNotEmpty())
            <div class="table-responsive payment-queue-table">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th scope="col">Booking</th><th scope="col" class="text-end">Booking total</th><th scope="col" class="text-end">Locked rate</th><th scope="col" class="text-end">Locked commission</th></tr></thead>
                    <tbody>
                        @foreach ($completedBookings as $booking)
                            <tr>
                                <th scope="row"><a href="{{ route('booking.show', $booking) }}">#{{ $booking->id }}</a></th>
                                <td class="text-end text-nowrap">{{ $booking->total_price === null ? '—' : $booking->total_price . ' JOD' }}</td>
                                <td class="text-end text-nowrap">{{ $booking->commission_rate === null ? '—' : $booking->commission_rate . '%' }}</td>
                                <td class="text-end text-nowrap fw-semibold">{{ $booking->commission_amount === null ? '—' : $booking->commission_amount . ' JOD' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="payment-queue-empty">
                <span aria-hidden="true"><i class="fa-regular fa-folder-open"></i></span>
                <h3 class="h6 workspace-heading">{{ $search !== '' ? 'No completed bookings match this search.' : 'No completed bookings yet.' }}</h3>
            </div>
        @endif
    </section>
    @if ($completedBookings->hasPages())
        <div class="mt-3">{{ $completedBookings->links('pagination::bootstrap-5') }}</div>
    @endif

    @if ($groups->isNotEmpty())
        <details class="card card-body mt-4 report-breakdown">
            <summary class="fw-semibold workspace-heading">Booking status breakdown</summary>
            <dl class="mb-0 mt-3">
                @foreach ($groups as $group)
                    <div class="d-flex justify-content-between gap-3 py-2">
                        <dt>{{ str($group->status)->headline() }}</dt><dd class="mb-0">{{ number_format($group->total) }}</dd>
                    </div>
                @endforeach
            </dl>
        </details>
    @endif
@endsection
