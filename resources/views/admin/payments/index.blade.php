@extends('layouts.dashboard')
@section('page-title', 'CliQ review')

@section('content')
    <p class="text-body-secondary">Review deposit transfer proofs submitted by customers.</p>
    <nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Payment status">
        @foreach (['pending', 'approved', 'rejected', 'all'] as $filter)
            <a @class(['move-filter', 'is-current' => $status === $filter]) @if ($status === $filter) aria-current="page" @endif
                href="{{ route('admin.payments.index', ['status' => $filter]) }}">
                {{ $filter === 'all' ? __('payments.filter_all') : __('payments.status_' . $filter) }}
            </a>
        @endforeach
    </nav>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('payments.booking') }}</th>
                    <th>{{ __('payments.customer') }}</th>
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

                        <td>
                            #{{ $payment->booking_id }}
                            @if ($payment->status === 'pending' && $payment->booking->deposit_due_at?->isPast())
                                <span class="badge text-bg-warning">{{ __('payments.overdue') }}</span>
                            @endif
                        </td>
                        <td>{{ $payment->booking->customer?->name }}</td>
                        <td><bdi>{{ $payment->amount }} {{ config('payments.currency') }}</bdi></td>
                        <td><bdi>{{ $payment->transfer_reference ?? '—' }}</bdi></td>
                        <td>@include('booking._status', ['state' => $payment->status, 'label' => __('payments.status_' . $payment->status)])</td>
                        <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-primary btn-sm"
                                href="{{ route('admin.payments.show', $payment->id) }}">{{ __('payments.review') }}</a>
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
@endsection
