@extends('layouts.dashboard')

@section('title', __('payments.pay_title'))
@section('page-title', __('payments.pay_title'))

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('payments.index') }}">{{ __('payments.back') }}</a>
@endsection

@section('content')
    <p class="text-body-secondary">Booking #{{ $booking->id }} · Transfer the deposit via CliQ and upload the receipt.</p>
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

    <div class="row g-4">
        <div class="col-lg-8 d-grid gap-3">
            <section class="card">
                <h2 class="card-header h6">Transfer details</h2>
                <dl class="card-body row mb-0">
                    <dt class="col-sm-5">{{ __('payments.beneficiary') }}</dt>
                    <dd class="col-sm-7">{{ $beneficiary['clik_name'] }}</dd>
                    <dt class="col-sm-5">{{ __('payments.clik_id') }}</dt>
                    <dd class="col-sm-7"><bdi>{{ $beneficiary['clik_id'] }}</bdi></dd>
                    <dt class="col-sm-5">{{ __('payments.deposit_required') }}</dt>
                    <dd class="col-sm-7"><strong><bdi>{{ $booking->deposit_amount }} {{ config('payments.currency') }}</bdi></strong></dd>
                    <dt class="col-sm-5">{{ __('payments.booking_number') }}</dt>
                    <dd class="col-sm-7 mb-0">#{{ $booking->id }}</dd>
                </dl>
            </section>
            <section class="card">
                <h2 class="card-header h6">Upload receipt</h2>
                <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data"
                    class="card-body">
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
            </section>
        </div>

        <aside class="col-lg-4">
            <div class="alert alert-warning" role="note">
                <i class="fa-solid fa-triangle-exclamation me-1" aria-hidden="true"></i>
                {{ __('payments.transfer_instructions') }}
            </div>
            @if ($previousProof)
                <section class="card">
                    <h2 class="card-header h6">Previous proof</h2>
                    <div class="card-body">
                        <strong>{{ ucfirst($previousProof->status) }}</strong>
                        @if ($previousProof->rejection_reason)
                            <p class="mb-0">{{ $previousProof->rejection_reason }}</p>
                        @endif
                    </div>
                </section>
            @endif
        </aside>
    </div>
@endsection
