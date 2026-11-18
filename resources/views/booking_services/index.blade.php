@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Booking services</h1>
        <a class="btn btn-primary" href="{{ route('booking-services.create') }}">Add service to booking</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="table-responsive border rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Booking</th>
                    <th>Service</th>
                    <th>Unit</th>
                    <th>Quantity</th>
                    <th>Unit price</th>
                    <th>Total</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookingServices as $bookingService)
                    <tr>
                        <td>{{ $bookingService->id }}</td>
                        <td>#{{ $bookingService->booking_id }}</td>
                        <td>{{ $bookingService->service_name }}</td>
                        <td>{{ $bookingService->unit }}</td>
                        <td>{{ $bookingService->quantity }}</td>
                        <td>{{ $bookingService->unit_price }}</td>
                        <td>{{ number_format($bookingService->quantity * $bookingService->unit_price, 3) }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm"
                                href="{{ route('booking-services.show', $bookingService->id) }}">Show</a>
                            <a class="btn btn-outline-primary btn-sm"
                                href="{{ route('booking-services.edit', $bookingService->id) }}">Edit</a>
                            <form method="POST" action="{{ route('booking-services.destroy', $bookingService->id) }}"
                                class="d-inline" onsubmit="return confirm('Delete this booking service?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3">No booking services yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $bookingServices->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
