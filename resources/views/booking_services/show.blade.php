@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('booking_services.details_title') }} #{{ $bookingService->id }}</h1>
        <div>
            @if (in_array(auth()->user()->role, ['admin', 'customer']) && $bookingService->booking->status === 'draft')
                <a class="btn btn-outline-primary" href="{{ route('booking-services.edit', $bookingService->id) }}">{{ __('booking_services.edit') }}</a>
            @endif
            <a class="btn btn-outline-secondary" href="{{ route('booking-services.index') }}">{{ __('booking_services.back') }}</a>
        </div>
    </div>

    <dl class="row border rounded bg-white p-3 p-md-4">
        @foreach ([
            __('booking_services.booking') => $bookingService->booking_id . ' · ' . ucfirst($bookingService->booking->status),
            __('booking_services.service') => $bookingService->service_name,
            __('booking_services.unit') => $bookingService->unit,
            __('booking_services.quantity') => $bookingService->quantity,
            __('booking_services.unit_price') => $bookingService->unit_price,
            __('booking_services.total') => $bookingService->lineTotal(),
            __('booking_services.created_at') => $bookingService->created_at?->format('Y-m-d H:i'),
            __('booking_services.updated_at') => $bookingService->updated_at?->format('Y-m-d H:i'),
        ] as $label => $value)
            <dt class="col-sm-4">{{ $label }}</dt>
            <dd class="col-sm-8 text-break">{{ $value ?? '—' }}</dd>
        @endforeach
    </dl>
</div>
@endsection
