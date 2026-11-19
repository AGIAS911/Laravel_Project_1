@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('payments.pay_title') }}</h1>
        <a class="btn btn-outline-secondary" href="{{ route('payments.index') }}">{{ __('payments.back') }}</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <p class="mb-1">{{ __('payments.check_fields') }}</p>
            <ul class="mb-0">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info">{{ __('payments.transfer_instructions') }}</div>

    <dl class="row border rounded bg-white p-3 p-md-4">
        <dt class="col-sm-4">{{ __('payments.beneficiary') }}</dt>
        <dd class="col-sm-8">{{ config('payments.clik_name') }}</dd>
        <dt class="col-sm-4">{{ __('payments.clik_id') }}</dt>
        <dd class="col-sm-8"><bdi>{{ config('payments.clik_id') }}</bdi></dd>
        <dt class="col-sm-4">{{ __('payments.booking_number') }}</dt>
        <dd class="col-sm-8">#{{ $booking->id }}</dd>
        <dt class="col-sm-4">{{ __('payments.deposit_required') }}</dt>
        <dd class="col-sm-8 mb-0"><bdi>{{ $booking->deposit_amount ?? '—' }} {{ config('payments.currency') }}</bdi></dd>
    </dl>

    <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data"
        class="border rounded bg-white p-3 p-md-4">
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="amount">{{ __('payments.claimed_amount') }}</label>
                <input class="form-control @error('amount') is-invalid @enderror" type="number" step="0.001"
                    min="0.001" id="amount" name="amount" value="{{ old('amount', $booking->deposit_amount) }}" required>
                @error('amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="transfer_reference">{{ __('payments.transfer_reference') }}</label>
                <input class="form-control @error('transfer_reference') is-invalid @enderror" type="text"
                    maxlength="100" id="transfer_reference" name="transfer_reference"
                    value="{{ old('transfer_reference') }}">
                @error('transfer_reference')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="receipt_image">{{ __('payments.receipt') }}</label>
                <input class="form-control @error('receipt_image') is-invalid @enderror" type="file"
                    accept="image/jpeg,image/png,image/webp" id="receipt_image" name="receipt_image" required>
                @error('receipt_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary" type="submit">{{ __('payments.submit') }}</button>
        </div>
    </form>
</div>
@endsection
