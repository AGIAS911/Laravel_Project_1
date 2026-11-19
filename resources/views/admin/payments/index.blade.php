@extends('layouts.dashboard')

@section('title', 'CliQ review | MoveIt')
@section('page-title', 'CliQ review')
@section('page-description', 'Review deposit receipts and track payment decisions.')

@section('content')
    @php
        $filters = ['pending' => 'Under review', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All proofs'];
        $currentQuery = array_filter(['status' => $status, 'overdue' => $overdueOnly]);
    @endphp

    <section class="payment-queue" aria-label="Payment proofs">
        <div class="payment-queue-toolbar">
            <nav class="d-flex flex-wrap gap-2" aria-label="Payment status">
                @foreach ($filters as $filter => $label)
                    <a @class(['move-filter', 'is-current' => $status === $filter])
                        @if ($status === $filter) aria-current="page" @endif
                        href="{{ route('admin.payments.index', array_filter(['status' => $filter, 'q' => $search], fn ($value) => $value !== '')) }}">
                        {{ $label }} ({{ $filter === 'all' ? $counts->sum() : ($counts[$filter] ?? 0) }})
                    </a>
                @endforeach
            </nav>
            <form class="payment-search" action="{{ route('admin.payments.index') }}" method="GET" role="search">
                @foreach ($currentQuery as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <input class="form-control" type="search" name="q" value="{{ $search }}" aria-label="Booking ID or transfer reference"
                    placeholder="Booking ID or reference" maxlength="100">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </form>
        </div>

        <div class="payment-queue-context">
            <div class="small text-body-secondary">
                {{ $payments->total() }} {{ str('proof')->plural($payments->total()) }}
                @if ($search !== '')
                    matching <strong>“{{ $search }}”</strong>
                    <a class="ms-1" href="{{ route('admin.payments.index', $currentQuery) }}">Clear search</a>
                @endif
            </div>
            @if ($status === 'pending' && ($overdueCount || $overdueOnly))
                <a @class(['payment-overdue-filter', 'is-current' => $overdueOnly])
                    href="{{ route('admin.payments.index', array_filter(['status' => 'pending', 'q' => $search, 'overdue' => $overdueOnly ? null : 1], fn ($value) => $value !== '' && $value !== null)) }}">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    {{ $overdueOnly ? 'Show all under review' : 'Overdue only (' . $overdueCount . ')' }}
                </a>
            @endif
        </div>

        @if ($payments->isNotEmpty())
            <div class="table-responsive payment-queue-table">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Booking / customer</th>
                            <th scope="col">Claimed amount</th>
                            <th scope="col">Transfer reference</th>
                            <th scope="col">Uploaded</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            @php($overdue = $payment->status === 'pending' && $payment->booking->deposit_due_at?->isPast())
                            <tr>
                                <th scope="row">
                                    <a class="fw-bold" href="{{ route('booking.show', $payment->booking_id) }}">#{{ $payment->booking_id }}</a>
                                    <span class="d-block small fw-normal text-body-secondary">{{ $payment->booking->customer?->name ?? '—' }}</span>
                                </th>
                                <td class="text-nowrap fw-semibold"><bdi>{{ $payment->amount }} {{ config('payments.currency') }}</bdi></td>
                                <td><bdi class="text-break">{{ $payment->transfer_reference ?? '—' }}</bdi></td>
                                <td class="text-nowrap">{{ $payment->created_at?->timezone('Asia/Amman')->format('d M Y · H:i') ?? '—' }}</td>
                                <td>
                                    @include('payments._status')
                                    @if ($overdue)<span class="payment-overdue-label"><i class="fa-regular fa-clock" aria-hidden="true"></i> Overdue</span>@endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a @class(['btn btn-sm', 'btn-primary' => $payment->status === 'pending', 'btn-outline-secondary' => $payment->status !== 'pending'])
                                        href="{{ route('admin.payments.show', $payment) }}">
                                        {{ $payment->status === 'pending' ? 'Review proof' : 'View decision' }}
                                        <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="payment-queue-empty">
                <span aria-hidden="true"><i class="fa-regular fa-folder-open"></i></span>
                <h2 class="h6 workspace-heading">{{ $search !== '' ? 'No proofs match this search.' : ($overdueOnly ? 'No overdue proofs.' : 'No proofs in this status.') }}</h2>
                @if ($search !== '' || $overdueOnly)
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.payments.index', ['status' => $status]) }}">Reset filters</a>
                @endif
            </div>
        @endif
    </section>
    @if ($payments->hasPages())
        <div class="mt-3">{{ $payments->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection
