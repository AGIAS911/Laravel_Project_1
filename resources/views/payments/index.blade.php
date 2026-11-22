@extends('layouts.dashboard')

@section('title', __('Payments'))
@section('page-title', __('Payments'))
@section('page-description', 'Manage your deposits and track the receipts you’ve submitted.')

@section('actions')
    <a class="btn btn-outline-secondary product-action" href="#payment-history">
        <i class="fa-solid fa-receipt" aria-hidden="true"></i> Receipt history
    </a>
@endsection

@section('content')
    @php
        $depositGroups = $awaiting->groupBy(fn ($booking) => match (true) {
            (bool) $booking->receipt_under_review => 'review',
            (bool) $booking->deposit_due_at?->isPast() => 'expired',
            default => 'due',
        });
        $dueDeposits = $depositGroups->get('due', collect())->sortBy(fn ($booking) => $booking->deposit_due_at?->timestamp ?? PHP_INT_MAX);
    @endphp

    <section class="card customer-payments-panel mb-4" aria-labelledby="payment-deposits">
        <header class="customer-payments-heading">
            <span class="customer-payment-icon" aria-hidden="true"><i class="fa-solid fa-wallet"></i></span>
            <h2 class="h5 workspace-heading mb-0" id="payment-deposits">Deposits awaiting payment</h2>
            <span class="customer-payment-count">{{ $dueDeposits->count() }}</span>
        </header>

        @if ($dueDeposits->isNotEmpty())
            <ul class="list-unstyled mb-0">
                @foreach ($dueDeposits as $booking)
                    @include('payments._deposit-row')
                @endforeach
            </ul>
        @else
            <p class="customer-payment-empty mb-0">No deposits currently due.</p>
        @endif

        @foreach (['review' => ['Receipt under review', 'fa-clock'], 'expired' => ['Deposit deadline passed', 'fa-calendar-xmark']] as $group => [$groupLabel, $groupIcon])
            @if ($deposits = $depositGroups->get($group))
                <details class="customer-payment-group">
                    <summary>
                        <span class="customer-payment-icon" aria-hidden="true"><i class="fa-regular {{ $groupIcon }}"></i></span>
                        <strong>{{ $groupLabel }}</strong>
                        <span class="customer-payment-count">{{ $deposits->count() }}</span>
                        <i class="fa-solid fa-chevron-down customer-payment-chevron" aria-hidden="true"></i>
                    </summary>
                    <ul class="list-unstyled mb-0">
                        @foreach ($deposits as $booking)
                            @include('payments._deposit-row')
                        @endforeach
                    </ul>
                </details>
            @endif
        @endforeach
    </section>

    <section class="card customer-payments-panel" id="payment-history" aria-labelledby="payment-history-title">
        <header class="customer-payments-heading">
            <span class="customer-payment-icon" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
            <h2 class="h5 workspace-heading mb-0" id="payment-history-title">Payment proof history</h2>
        </header>
        @if ($payments->isNotEmpty())
            <div class="table-responsive">
                <table class="table align-middle mb-0 customer-receipt-table" role="table" aria-labelledby="payment-history-title">
                    <thead role="rowgroup">
                        <tr role="row">
                            <th scope="col" role="columnheader">Booking</th>
                            <th scope="col" role="columnheader">Amount</th>
                            <th scope="col" role="columnheader">Reference</th>
                            <th scope="col" role="columnheader">Status</th>
                            <th scope="col" role="columnheader">Uploaded (Amman)</th>
                            <th scope="col" role="columnheader"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody role="rowgroup">
                        @foreach ($payments as $payment)
                            <tr role="row">
                                <th class="customer-receipt-booking" scope="row" role="rowheader">
                                    <a href="{{ route('booking.show', $payment->booking_id) }}">Booking #{{ $payment->booking_id }}</a>
                                    <small class="d-block text-body-secondary fw-normal">Receipt #{{ $payment->id }}</small>
                                </th>
                                <td class="customer-receipt-amount" role="cell"><bdi>{{ $payment->amount }} {{ config('payments.currency') }}</bdi></td>
                                <td class="customer-receipt-reference" role="cell">
                                    <span class="customer-receipt-mobile-label">Reference</span><bdi>{{ $payment->transfer_reference ?? '—' }}</bdi>
                                </td>
                                <td class="customer-receipt-status" role="cell">
                                    @include('payments._status')
                                    @if ($payment->status === 'rejected' && $payment->rejection_reason)
                                        <p class="text-danger small mt-1 mb-0">{{ $payment->rejection_reason }}</p>
                                    @endif
                                </td>
                                <td class="customer-receipt-date" role="cell">
                                    <span class="customer-receipt-mobile-label">Uploaded (Amman)</span>
                                    @if ($payment->created_at)
                                        <time datetime="{{ $payment->created_at->toIso8601String() }}">{{ $payment->created_at->timezone(config('booking.timezone'))->format('d M Y, H:i') }}</time>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="customer-receipt-action" role="cell">
                                    <a class="btn btn-outline-secondary product-action" target="_blank" rel="noopener"
                                        data-popup-image="Receipt #{{ $payment->id }}"
                                        href="{{ route('payments.receipt', $payment->id) }}">
                                        <i class="fa-regular fa-image" aria-hidden="true"></i> View receipt
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="customer-payment-empty mb-0">No payment receipts yet.</p>
        @endif
    </section>

    <div class="mt-3">{{ $payments->fragment('payment-history')->links('pagination::bootstrap-5') }}</div>
@endsection
