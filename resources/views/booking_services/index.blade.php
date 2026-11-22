@extends('layouts.app')

@section('content')
@php $canEdit = in_array(auth()->user()->role, ['admin', 'customer']); @endphp
<div class="container">
    <header class="product-page-header">
        <h1 class="product-title">{{ __('booking_services.title') }}</h1>
        @if ($canEdit)
            <a class="btn btn-primary" href="{{ route('booking-services.create') }}">{{ __('booking_services.add') }}</a>
        @endif
    </header>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @error('service')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    <div class="table-responsive card">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('booking_services.booking') }}</th>
                    <th>{{ __('booking_services.service') }}</th>
                    <th>{{ __('booking_services.unit') }}</th>
                    <th>{{ __('booking_services.quantity') }}</th>
                    <th>{{ __('booking_services.unit_price') }}</th>
                    <th>{{ __('booking_services.total') }}</th>
                    <th class="text-end">{{ __('booking_services.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookingServices as $bookingService)
                    <tr>
                        <td>{{ $bookingService->id }}</td>
                        <td>{{ $bookingService->booking_id }}</td>
                        <td>{{ $bookingService->service_name }}</td>
                        <td>{{ $bookingService->unit }}</td>
                        <td>{{ $bookingService->quantity }}</td>
                        <td>{{ $bookingService->unit_price }}</td>
                        <td>{{ $bookingService->lineTotal() }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm"
                                href="{{ route('booking-services.show', $bookingService->id) }}">{{ __('booking_services.show') }}</a>
                            @if ($canEdit && $bookingService->booking->status === 'draft')
                                <a class="btn btn-outline-primary btn-sm"
                                    href="{{ route('booking-services.edit', $bookingService->id) }}">{{ __('booking_services.edit') }}</a>
                                <form method="POST" action="{{ route('booking-services.destroy', $bookingService->id) }}"
                                    class="d-inline" data-popup-title="Remove this service?" data-popup-action="Remove service" data-popup-danger>
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" type="submit">{{ __('booking_services.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3">{{ __('booking_services.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $bookingServices->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
