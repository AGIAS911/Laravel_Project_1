@extends('layouts.dashboard')

@section('title', __('Payments'))
@section('page-title', __('Payments'))
@section('page-icon', 'fa-receipt')

@section('content')
    <p class="text-body-secondary">Payment proof history and deposits awaiting payment.</p>

    @if ($awaiting->isNotEmpty())
        <div class="card card-body customer-panel mb-4">
            <h2 class="h6">{{ __('Deposits awaiting payment') }}</h2>
            <ul class="list-unstyled mb-0">
                @foreach ($awaiting as $booking)
                    <li class="customer-payment-due d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                        <span>
                            {{ $booking->id }} · {{ __('Required deposit') }}:
                            <bdi>{{ $booking->deposit_amount ?? '—' }} {{ config('payments.currency') }}</bdi>
                            @if ($booking->deposit_due_at)
                                · {{ __('Due') }}: {{ $booking->deposit_due_at->timezone('Asia/Amman')->format('Y-m-d H:i') }}
                            @endif
                        </span>
                        @if ($booking->receipt_under_review)
                            @include('booking._status', ['state' => 'pending', 'label' => 'Receipt under review', 'icon' => 'fa-clock'])
                        @elseif ($booking->deposit_due_at?->isPast())
                            <span class="badge text-bg-warning">Deposit deadline passed</span>
                        @elseif (!$cliqConfigured)
                            <a class="btn btn-outline-secondary btn-sm"
                                href="{{ route('payments.create', ['booking_id' => $booking->id]) }}">Payment unavailable · Get help</a>
                        @else
                            <a class="btn btn-primary btn-sm"
                                href="{{ route('payments.create', ['booking_id' => $booking->id]) }}">{{ __('Pay deposit') }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="alert alert-info d-flex align-items-start gap-2" role="note">
            <i class="fa-solid fa-circle-info mt-1" aria-hidden="true"></i>
            <div>
                <strong>No deposit payment is due yet.</strong>
                <p class="mb-2">The Pay deposit option appears here after your driver accepts a booking.</p>
                <a href="{{ route('booking.index') }}">View my bookings</a>
            </div>
        </div>
    @endif

    <div class="card move-table">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Booking') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Transfer reference') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Uploaded') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td><a href="{{ route('booking.show', $payment->booking_id) }}">{{ $payment->booking_id }}</a></td>
                        <td><bdi>{{ $payment->amount }} {{ config('payments.currency') }}</bdi></td>
                        <td><bdi>{{ $payment->transfer_reference ?? '—' }}</bdi></td>
                        <td>
                            @include('payments._status')
                            @if ($payment->status === 'rejected' && $payment->rejection_reason)
                                <div class="text-danger small">{{ $payment->rejection_reason }}</div>
                            @endif
                        </td>
                        <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm" target="_blank"
                                href="{{ route('payments.receipt', $payment->id) }}">{{ __('View receipt') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3">{{ __('No payment receipts yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-3">{{ $payments->links('pagination::bootstrap-5') }}</div>
@endsection
