@extends('layouts.dashboard')

@section('title', __('Pay deposit'))
@section('page-title', __('Pay deposit'))
@section('page-icon', 'fa-money-check-dollar')
@section('page-description')
    @if ($cliqConfigured)
        Transfer your deposit through your banking app, then upload the receipt for verification.
    @else
        View the deposit status for your booking.
    @endif
@endsection

@section('actions')
    <a class="btn btn-outline-secondary product-action" href="{{ route('payments.index') }}">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ __('Back to payments') }}
    </a>
@endsection

@section('content')
    <a class="deposit-booking-link" href="{{ route('booking.show', $booking) }}">
        <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i> Booking #{{ $booking->id }}
    </a>

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

        <div class="deposit-checkout">
            <div class="deposit-checkout-column">
                <section class="card card-body product-transfer-details" aria-labelledby="transfer-title">
                    <header class="deposit-step-heading">
                        <span class="deposit-step-number" aria-hidden="true">1</span>
                        <h2 class="h5 workspace-heading mb-0" id="transfer-title">Transfer via CliQ</h2>
                    </header>
                    <div class="deposit-amount-panel">
                        <div>
                            <span class="small">{{ __('Deposit balance') }}</span>
                            <p class="deposit-amount-display mb-0"><bdi>{{ $remainingDeposit }} <small>{{ config('payments.currency') }}</small></bdi></p>
                        </div>
                        <button type="button" class="btn btn-outline-secondary product-action" data-deposit-copy="{{ $remainingDeposit }}" data-copy-label="Amount">
                            <i class="fa-regular fa-copy" aria-hidden="true"></i> Copy amount
                        </button>
                    </div>
                    <dl class="deposit-transfer-list mb-0">
                        <div>
                            <dt>{{ __('Beneficiary') }}</dt>
                            <dd class="mb-0"><bdi>{{ $beneficiary['clik_name'] }}</bdi></dd>
                        </div>
                        <div>
                            <dt>{{ __('CliQ alias') }}</dt>
                            <dd class="deposit-alias-row mb-0">
                                <bdi>{{ $beneficiary['clik_id'] }}</bdi>
                                <button type="button" class="btn btn-outline-secondary product-action" data-deposit-copy="{{ $beneficiary['clik_id'] }}" data-copy-label="CliQ alias">
                                    <i class="fa-regular fa-copy" aria-hidden="true"></i> Copy alias
                                </button>
                            </dd>
                        </div>
                        @if ($booking->deposit_due_at)
                            <div>
                                <dt><i class="fa-regular fa-clock me-1" aria-hidden="true"></i> Pay by (Amman time)</dt>
                                <dd class="mb-0"><time datetime="{{ $booking->deposit_due_at->toIso8601String() }}">{{ $booking->deposit_due_at->timezone(config('booking.timezone'))->format('d M Y, H:i') }}</time></dd>
                            </div>
                        @endif
                    </dl>
                    <p class="small text-body-secondary mb-0 mt-2" data-deposit-copy-status role="status" hidden></p>
                </section>

                <section class="card card-body deposit-next" aria-labelledby="deposit-next-title">
                    <h2 class="h6 workspace-heading" id="deposit-next-title"><i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i> What happens next</h2>
                    <p class="small text-body-secondary mb-0">
                        @if ($booking->driver_id && $booking->vehicle_id)
                            Your booking is confirmed when the team verifies the full deposit.
                        @else
                            Once the team verifies the full deposit, we look for a driver. Their details appear on your booking after they accept.
                        @endif
                    </p>
                </section>
            </div>

            <div class="deposit-checkout-column">
                <section class="card card-body deposit-receipt-card" aria-labelledby="upload-title">
                    <header class="deposit-step-heading">
                        <span class="deposit-step-number" aria-hidden="true">2</span>
                        <h2 class="h5 workspace-heading mb-0" id="upload-title">Upload receipt</h2>
                    </header>
                    <p class="small text-body-secondary">Enter the amount you transferred and attach the receipt from your banking app.</p>
                    <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="amount">{{ __('Transferred amount') }}</label>
                                <div class="input-group">
                                    <input class="form-control @error('amount') is-invalid @enderror" type="number" step="0.001"
                                        min="0.001" id="amount" name="amount" inputmode="decimal" value="{{ old('amount', $remainingDeposit) }}" required>
                                    <span class="input-group-text">{{ config('payments.currency') }}</span>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label" for="transfer_reference">{{ __('Transfer reference') }} <span class="small text-body-secondary fw-normal">(optional)</span></label>
                                <input class="form-control @error('transfer_reference') is-invalid @enderror" type="text"
                                    maxlength="100" id="transfer_reference" name="transfer_reference"
                                    value="{{ old('transfer_reference') }}">
                                @error('transfer_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="deposit-upload-field">
                                    <label class="form-label" for="receipt_image"><i class="fa-solid fa-file-arrow-up me-1" aria-hidden="true"></i> {{ __('Receipt image') }}</label>
                                    <input class="form-control @error('receipt_image') is-invalid @enderror" type="file"
                                        accept="image/jpeg,image/png,image/webp" id="receipt_image" name="receipt_image" required aria-describedby="receipt-file-help">
                                    <p class="form-text mb-0" id="receipt-file-help">JPEG, PNG or WebP, up to 4 MB.</p>
                                    @error('receipt_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="deposit-file-preview mt-3" data-deposit-preview hidden>
                                        <img alt="Selected receipt preview">
                                        <button class="btn btn-outline-secondary product-action" type="button" data-deposit-clear>
                                            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Remove attachment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary product-action deposit-submit" type="submit">
                            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i> {{ __('Submit receipt') }}
                        </button>
                    </form>
                </section>

                @if ($previousProof)
                    <section class="card card-body" aria-labelledby="previous-proof-title">
                        <h2 class="h6 workspace-heading" id="previous-proof-title">Previous proof</h2>
                        <strong>{{ ucfirst($previousProof->status) }}</strong>
                        @if ($previousProof->rejection_reason)
                            <p class="small text-body-secondary mb-0">{{ $previousProof->rejection_reason }}</p>
                        @endif
                    </section>
                @endif
            </div>
        </div>

        <script src="{{ asset('js/deposit-receipt.js') }}" defer></script>
    @endif
@endsection
