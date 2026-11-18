@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('payments.details_title') }} #{{ $payment->id }}</h1>
        <a class="btn btn-outline-secondary" href="{{ route('admin.payments.index') }}">{{ __('payments.back') }}</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <dl class="row border rounded bg-white p-3 p-md-4">
        @foreach ([
            __('payments.booking') => '#' . $payment->booking_id . ' · ' . ucfirst($payment->booking->status),
            __('payments.customer') => $payment->booking->customer?->name,
            __('payments.deposit_required') => $payment->booking->deposit_amount,
            __('payments.approved_total') => number_format($approvedTotal, 3, '.', ''),
            __('payments.deposit_due') => $payment->booking->deposit_due_at?->format('Y-m-d H:i'),
            __('payments.amount') => $payment->amount,
            __('payments.transfer_reference') => $payment->transfer_reference,
            __('payments.status') => __('payments.status_' . $payment->status),
            __('payments.reason') => $payment->rejection_reason,
            __('payments.reviewed_by') => $payment->reviewer?->name,
            __('payments.reviewed_at') => $payment->reviewed_at?->format('Y-m-d H:i'),
            __('payments.created_at') => $payment->created_at?->format('Y-m-d H:i'),
        ] as $label => $value)
            <dt class="col-sm-4">{{ $label }}</dt>
            <dd class="col-sm-8 text-break"><bdi>{{ $value ?? '—' }}</bdi></dd>
        @endforeach
    </dl>

    <div class="border rounded bg-white p-3 mb-3">
        <img src="{{ route('payments.receipt', $payment->id) }}" alt="{{ __('payments.view_receipt') }}"
            class="img-fluid" style="max-height: 480px;">
    </div>

    @if ($payment->status === 'pending')
        <div class="row g-3">
            <div class="col-md-6">
                <form method="POST" action="{{ route('admin.payments.approve', $payment->id) }}"
                    class="border rounded bg-white p-3">
                    @csrf
                    <label class="form-label" for="amount">{{ __('payments.approve_amount') }}</label>
                    <input class="form-control mb-2" type="number" step="0.001" min="0.001" id="amount"
                        name="amount" value="{{ old('amount', $payment->amount) }}" required>
                    <button class="btn btn-success" type="submit">{{ __('payments.approve') }}</button>
                </form>
            </div>
            <div class="col-md-6">
                <form method="POST" action="{{ route('admin.payments.reject', $payment->id) }}"
                    class="border rounded bg-white p-3">
                    @csrf
                    <label class="form-label" for="rejection_reason">{{ __('payments.reject_reason') }}</label>
                    <textarea class="form-control mb-2" id="rejection_reason" name="rejection_reason" rows="2"
                        maxlength="1000" required>{{ old('rejection_reason') }}</textarea>
                    <button class="btn btn-danger" type="submit">{{ __('payments.reject') }}</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
