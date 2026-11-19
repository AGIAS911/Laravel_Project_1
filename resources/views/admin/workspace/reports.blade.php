@extends('layouts.dashboard')
@section('page-title', 'Commission reports')

@section('content')
    <div class="admin-metrics admin-report-metrics mb-4">
        @foreach ([
            ['Recorded commission', number_format($recordedCommission, 3) . ' JOD', 'fa-coins'],
            ['Approved deposits', number_format($approvedDeposits, 3) . ' JOD', 'fa-money-check-dollar'],
            ['Completed bookings', $groups->firstWhere('status', 'completed')?->total ?? 0, 'fa-clipboard-check'],
        ] as [$label, $value, $icon])
            <article class="admin-metric">
                <span class="admin-metric-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                <div><strong>{{ $value }}</strong><span>{{ $label }}</span></div>
            </article>
        @endforeach
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th scope="col">Booking</th><th scope="col">Total</th><th scope="col">Locked commission</th></tr></thead>
            <tbody>
                @forelse ($completedBookings as $booking)
                    <tr>
                        <th scope="row"><a href="{{ route('booking.show', $booking) }}">#{{ $booking->id }}</a></th>
                        <td>{{ $booking->total_price === null ? '—' : $booking->total_price . ' JOD' }}</td>
                        <td>{{ $booking->commission_amount === null ? '—' : $booking->commission_amount . ' JOD' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-4">No completed bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $completedBookings->links('pagination::bootstrap-5') }}</div>

    <section class="card card-body mt-4">
        <h2 class="h5">Booking status totals</h2>
        <dl class="row mb-0">
            @foreach ($groups as $group)
                <dt class="col-8">{{ str($group->status)->headline() }}</dt><dd class="col-4">{{ $group->total }}</dd>
            @endforeach


        </dl>
    </section>
@endsection
