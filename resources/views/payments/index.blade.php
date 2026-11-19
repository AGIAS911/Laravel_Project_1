@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h4 mb-3">{{ __('payments.title') }}</h1>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if ($awaiting->isNotEmpty())
        <div class="border rounded bg-white p-3 mb-4">
            <h2 class="h6">{{ __('payments.awaiting') }}</h2>
            <ul class="list-unstyled mb-0">
                @foreach ($awaiting as $booking)
                    <li class="d-flex justify-content-between align-items-center py-1">
                        <span>
                            #{{ $booking->id }} · {{ __('payments.deposit_required') }}:
                            <bdi>{{ $booking->deposit_amount ?? '—' }}</bdi>
                            @if ($booking->deposit_due_at)
                                · {{ __('payments.deposit_due') }}: {{ $booking->deposit_due_at->format('Y-m-d H:i') }}
                            @endif
                        </span>
                        <a class="btn btn-primary btn-sm"
                            href="{{ route('payments.create', ['booking_id' => $booking->id]) }}">{{ __('payments.pay_now') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-responsive border rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('payments.booking') }}</th>
                    <th>{{ __('payments.amount') }}</th>
                    <th>{{ __('payments.transfer_reference') }}</th>
                    <th>{{ __('payments.status') }}</th>
                    <th>{{ __('payments.created_at') }}</th>
                    <th class="text-end">{{ __('payments.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>#{{ $payment->booking_id }}</td>
                        <td><bdi>{{ $payment->amount }}</bdi></td>
                        <td><bdi>{{ $payment->transfer_reference ?? '—' }}</bdi></td>
                        <td>
                            {{ __('payments.status_' . $payment->status) }}
                            @if ($payment->status === 'rejected' && $payment->rejection_reason)
                                <div class="text-danger small">{{ $payment->rejection_reason }}</div>
                            @endif
                        </td>
                        <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm" target="_blank"
                                href="{{ route('payments.receipt', $payment->id) }}">{{ __('payments.view_receipt') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3">{{ __('payments.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $payments->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
