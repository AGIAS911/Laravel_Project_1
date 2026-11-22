@extends('layouts.app')

@section('content')
<div class="container">
    <header class="product-page-header">
        <h1 class="product-title">{{ __('booking_services.edit_title') }} #{{ $bookingService->id }}</h1>
        <a class="btn btn-outline-secondary" href="{{ route('booking-services.index') }}">{{ __('booking_services.back') }}</a>
    </header>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <p class="mb-1">{{ __('booking_services.check_fields') }}</p>
            <ul class="mb-0">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('booking-services.update', $bookingService->id) }}"
        class="card card-body product-editor">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('booking_services.booking') }}</label>
                <input class="form-control" value="{{ $bookingService->booking_id }}" disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('booking_services.service') }}</label>
                <input class="form-control" value="{{ $bookingService->service_name }} ({{ $bookingService->unit_price }} / {{ $bookingService->unit }})" disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="quantity">{{ __('booking_services.quantity') }}</label>
                <input class="form-control @error('quantity') is-invalid @enderror" type="number" step="1"
                    min="1" id="quantity" name="quantity"
                    value="{{ old('quantity', $bookingService->quantity) }}" required>
                @error('quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary" type="submit">{{ __('booking_services.update') }}</button>
        </div>
    </form>
</div>
@endsection
