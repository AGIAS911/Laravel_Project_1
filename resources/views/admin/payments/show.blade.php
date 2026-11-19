@extends('layouts.dashboard')

@section('page-title', 'Review proof #' . $payment->id)

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.payments.index') }}">{{ __('payments.back') }}</a>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <section class="col-lg-7">
            <div class="card card-body">
                <h2 class="h5 workspace-heading">Receipt image</h2>
                <a href="{{ route('payments.receipt', $payment->id) }}" target="_blank" rel="noopener">
                    <img src="{{ route('payments.receipt', $payment->id) }}" alt="{{ __('payments.view_receipt') }}" class="img-fluid rounded" style="max-height:480px">
                </a>
            </div>
        </section>
        <div class="col-lg-5">
            <section class="card card-body mb-3">
                <h2 class="h5 workspace-heading">Transfer details</h2>
    <dl class="row mb-0">
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

            </section>
    @if ($payment->status === 'pending')
        <div class="d-grid gap-3">
            <div >
                <form method="POST" action="{{ route('admin.payments.approve', $payment->id) }}"
                    class="card card-body">
                    @csrf
                    <label class="form-label" for="amount">{{ __('payments.approve_amount') }}</label>
                    <input class="form-control mb-2" type="number" step="0.001" min="0.001" id="amount"
                        name="amount" value="{{ old('amount', $payment->amount) }}" required>
                    <button class="btn btn-success" type="submit">{{ __('payments.approve') }}</button>
                </form>
            </div>
            <div >
                <form method="POST" action="{{ route('admin.payments.reject', $payment->id) }}"
                    class="card card-body">
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
    </div>
@endsection
