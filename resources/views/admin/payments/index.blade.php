@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h4 mb-3">{{ __('payments.admin_title') }}</h1>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        @foreach (['pending', 'approved', 'rejected', 'all'] as $filter)
            <a class="btn btn-sm {{ $status === $filter ? 'btn-primary' : 'btn-outline-primary' }}"
                href="{{ route('admin.payments.index', ['status' => $filter]) }}">
                {{ $filter === 'all' ? __('payments.filter_all') : __('payments.status_' . $filter) }}
            </a>
        @endforeach
    </div>

    <div class="table-responsive border rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
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
                        <td>{{ $payment->id }}</td>
                        <td>
                            #{{ $payment->booking_id }}
                            @if ($payment->status === 'pending' && $payment->booking->deposit_due_at?->isPast())
                                <span class="badge text-bg-warning">{{ __('payments.overdue') }}</span>
                            @endif
                        </td>
                        <td>{{ $payment->booking->customer?->name }}</td>
                        <td><bdi>{{ $payment->amount }}</bdi></td>
                        <td><bdi>{{ $payment->transfer_reference ?? '—' }}</bdi></td>
                        <td>{{ __('payments.status_' . $payment->status) }}</td>
                        <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-primary btn-sm"
                                href="{{ route('admin.payments.show', $payment->id) }}">{{ __('payments.review') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3">{{ __('payments.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $payments->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
