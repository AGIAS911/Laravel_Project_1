@extends('layouts.dashboard')

@section('page-title', 'Review proof #' . $payment->id)
@section('page-description', 'Compare the receipt with the incoming CliQ transfer before recording a decision.')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.payments.index', ['status' => $payment->status]) }}">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> Back to CliQ review
    </a>
@endsection

@section('content')
    @php
        $booking = $payment->booking;
        $currency = config('payments.currency');
        $receiptUrl = route('payments.receipt', $payment);
        $pending = $payment->status === 'pending';
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        @foreach ([
            'Claimed amount' => $payment->amount,
            'Required deposit' => $booking->deposit_amount,
            'Approved deposit total' => number_format($approvedTotal, 3, '.', ''),
        ] as $label => $amount)
            <div class="col-sm-4">
                <div class="payment-metric">
                    <span>{{ $label }}</span>
                    <strong>{{ $amount === null ? '—' : $amount . ' ' . $currency }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-7">
            <section class="card card-body payment-panel">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h5 workspace-heading mb-0">Receipt image</h2>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ $receiptUrl }}" target="_blank" rel="noopener">
                        <i class="fa-solid fa-up-right-from-square me-1" aria-hidden="true"></i> Open full size
                    </a>
                </div>
                <a class="payment-receipt" href="{{ $receiptUrl }}" target="_blank" rel="noopener" aria-label="Open receipt image full size">
                    <img src="{{ $receiptUrl }}" alt="CliQ receipt for booking {{ $booking->id }}">
                </a>
            </section>
        </div>
        <div class="col-lg-5 d-grid gap-3">
            <section class="card card-body payment-panel">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h5 workspace-heading mb-0">Transfer details</h2>
                    @include('payments._status')
                </div>
                <dl class="payment-details mb-0">
                    <dt>Booking</dt><dd><a href="{{ route('booking.show', $booking) }}">{{ $booking->id }}</a> · {{ str($booking->status)->headline() }}</dd>
                    <dt>Customer</dt><dd>{{ $booking->customer?->name ?? '—' }}</dd>
                    <dt>Transfer reference</dt><dd><bdi>{{ $payment->transfer_reference ?: '—' }}</bdi></dd>
                    <dt>Uploaded</dt><dd>{{ $payment->created_at?->timezone('Asia/Amman')->format('d M Y · H:i') ?? '—' }}</dd>
                    <dt>Deposit deadline</dt><dd>{{ $booking->deposit_due_at?->timezone('Asia/Amman')->format('d M Y · H:i') ?? '—' }}</dd>
                    @unless ($pending)
                        <dt>Reviewed by</dt><dd>{{ $payment->reviewer?->name ?? '—' }}</dd>
                        <dt>Reviewed at</dt><dd>{{ $payment->reviewed_at?->timezone('Asia/Amman')->format('d M Y · H:i') ?? '—' }}</dd>
                        @if ($payment->rejection_reason)
                            <dt>Rejection reason</dt><dd>{{ $payment->rejection_reason }}</dd>
                        @endif
                    @endunless
                </dl>
            </section>

            @if ($pending)
                <section class="card card-body payment-panel">
                    <h2 class="h5 workspace-heading">Record a decision</h2>
                    <p class="small text-body-secondary mb-3">Enter the amount you verified in the receiving account.</p>
                    <form method="POST" action="{{ route('admin.payments.approve', $payment) }}">
                        @csrf
                        <label class="form-label" for="amount">Verified amount ({{ $currency }})</label>
                        <input class="form-control mb-3" type="number" step="0.001" min="0.001" max="9999999" id="amount"
                            name="amount" value="{{ old('amount') }}" required>
                        <button class="btn btn-success w-100">Approve verified payment</button>
                    </form>
                    <details class="payment-reject mt-3" @if ($errors->has('rejection_reason')) open @endif>
                        <summary class="text-danger fw-semibold">Reject this proof</summary>
                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="pt-3">
                            @csrf
                            <label class="form-label" for="rejection_reason">Reason shown to the customer</label>
                            <textarea class="form-control mb-3" id="rejection_reason" name="rejection_reason" rows="3"
                                maxlength="1000" required>{{ old('rejection_reason') }}</textarea>
                            <button class="btn btn-outline-danger">Reject proof</button>
                        </form>
                    </details>
                </section>
            @endif
        </div>
    </div>
@endsection
