@extends('layouts.dashboard')

@section('title', __('Pay deposit'))
@section('page-title', __('Pay deposit'))
@section('page-icon', 'fa-money-check-dollar')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('payments.index') }}">{{ __('Back to payments') }}</a>
@endsection

@section('content')

    <p class="text-body-secondary"><a href="{{ route('booking.show', $booking) }}">Booking #{{ $booking->id }}</a>@if ($cliqConfigured) · Transfer the deposit via CliQ and upload the receipt.@endif</p>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <p class="mb-1">{{ __('Check the highlighted fields.') }}</p>
            <ul class="mb-0">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!$cliqConfigured)
        <div class="alert alert-warning" role="alert">
            <h2 class="h6">CliQ is temporarily unavailable</h2>
            <p class="mb-0">Ask <a href="{{ route('info.contact') }}">support</a> for help with the deposit for this booking.</p>
        </div>
    @else
    @php($remainingDeposit = $booking->remainingDeposit())
    <div class="row g-4">
        <div class="col-lg-8 d-grid gap-3">
            <section class="card">
                <h2 class="card-header h6 workspace-heading">Transfer details</h2>
                <dl class="card-body row mb-0">
                    <dt class="col-sm-5">{{ __('Beneficiary') }}</dt>
                    <dd class="col-sm-7">{{ $beneficiary['clik_name'] }}</dd>
                    <dt class="col-sm-5">{{ __('CliQ alias') }}</dt>
                    <dd class="col-sm-7"><bdi>{{ $beneficiary['clik_id'] }}</bdi></dd>
                    <dt class="col-sm-5">{{ __('Deposit balance') }}</dt>
                    <dd class="col-sm-7"><strong class="customer-deposit-amount"><bdi>{{ $remainingDeposit }} {{ config('payments.currency') }}</bdi></strong></dd>
                    <dt class="col-sm-5">{{ __('Booking') }}</dt>
                    <dd class="col-sm-7 mb-0">{{ $booking->id }}</dd>
                </dl>
            </section>
            <section class="card">
                <h2 class="card-header h6 workspace-heading">Upload receipt</h2>
                <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data"
                    class="card-body">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="amount">{{ __('Transferred amount') }}</label>
                            <input class="form-control @error('amount') is-invalid @enderror" type="number" step="0.001"
                                min="0.001" id="amount" name="amount" value="{{ old('amount', $remainingDeposit) }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="transfer_reference">{{ __('Transfer reference') }}</label>
                            <input class="form-control @error('transfer_reference') is-invalid @enderror" type="text"
                                maxlength="100" id="transfer_reference" name="transfer_reference"
                                value="{{ old('transfer_reference') }}">
                            @error('transfer_reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="receipt_image">{{ __('Receipt image') }}</label>
                            <input class="form-control @error('receipt_image') is-invalid @enderror" type="file"
                                accept="image/jpeg,image/png,image/webp" id="receipt_image" name="receipt_image" required>
                            @error('receipt_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-cloud-arrow-up me-1" aria-hidden="true"></i> {{ __('Submit receipt') }}</button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="col-lg-4">
            <div class="alert alert-info" role="note">
                <i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i>
                @if ($booking->driver_id && $booking->vehicle_id)
                    Your booking is confirmed when the team verifies the full deposit.
                @else
                    After the team verifies the full deposit, we look for a driver. You can see their details once they accept.
                @endif
            </div>
            @if ($previousProof)
                <section class="card">
                    <h2 class="card-header h6 workspace-heading">Previous proof</h2>
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
    @endif
@endsection
