<section @class(['product-review-checkout' => $wizardReview, 'card card-body mb-3 product-quote-section' => !$wizardReview]) aria-labelledby="review" data-workflow-scope>
    <h2 @class(['workspace-heading', $wizardReview ? 'h6' : 'h5']) id="review">Review and send</h2>

    @if (!\App\Models\PlatformSetting::quoteConfigured())
        <p class="alert alert-warning mb-0">Route pricing is temporarily unavailable. Your draft is saved. <a href="{{ route('info.contact') }}">Contact support</a> for help.</p>
    @else
        @if ($checkoutReady)
            <p class="small text-body-secondary mb-3">Review the total and deposit before continuing. We look for a driver after your deposit is verified.</p>
        @else
            <p class="alert alert-warning">CliQ checkout is temporarily unavailable. You can still see the moving price here. Your draft is saved.</p>
        @endif

        <p class="booking-workflow-message" data-workflow-status role="status"></p>
        @unless ($wizardReview)
            <div class="booking-quote mb-3" data-booking-quote hidden aria-live="polite">
                <div><span>Road distance</span><strong data-quote-distance></strong></div>
                <div><span>Total</span><strong data-quote-total></strong></div>
                <div><span>Deposit</span><strong data-quote-deposit></strong></div>
            </div>
        @endunless

        <div @class([$wizardReview ? 'd-grid gap-2' : 'd-flex flex-wrap gap-2'])>
            <form data-workflow-form="quote" method="POST" action="{{ route('bookings.quote', $booking, false) }}" hidden>
                @csrf
            </form>

            @if ($checkoutReady)
                <form id="submit-booking" data-workflow-form method="POST" action="{{ route('bookings.submit', $booking, false) }}" hidden>
                    @csrf
                    <input type="hidden" name="expected_total">
                    <button class="btn btn-primary product-action" type="submit">
                        <i class="fa-solid fa-wallet" aria-hidden="true"></i> Continue to CliQ payment
                    </button>
                </form>
            @endif
        </div>
    @endif
</section>
