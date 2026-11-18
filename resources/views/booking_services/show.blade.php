@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Booking service #{{ $bookingService->id }}</h1>
        <div>
            <a class="btn btn-outline-primary" href="{{ route('booking-services.edit', $bookingService->id) }}">Edit</a>
            <a class="btn btn-outline-secondary" href="{{ route('booking-services.index') }}">Back</a>
        </div>
    </div>

    <dl class="row border rounded bg-white p-3 p-md-4">
        @foreach ([
            'Booking' => '#' . $bookingService->booking_id . ' · ' . ucfirst($bookingService->booking->status),
            'Service' => $bookingService->service_name,
            'Unit' => $bookingService->unit,
            'Quantity' => $bookingService->quantity,
            'Unit price' => $bookingService->unit_price,
            'Total' => number_format($bookingService->quantity * $bookingService->unit_price, 3),
            'Created at' => $bookingService->created_at?->format('Y-m-d H:i'),
            'Updated at' => $bookingService->updated_at?->format('Y-m-d H:i'),
        ] as $label => $value)
            <dt class="col-sm-4">{{ $label }}</dt>
            <dd class="col-sm-8 text-break">{{ $value ?? '—' }}</dd>
        @endforeach
    </dl>
</div>
@endsection
