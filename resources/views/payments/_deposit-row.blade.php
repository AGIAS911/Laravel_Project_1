<li class="customer-deposit-row">
    <div class="customer-deposit-booking">
        <a class="fw-semibold" href="{{ route('booking.show', $booking) }}">Booking #{{ $booking->id }}</a>
        @if ($booking->deposit_due_at)
            <span class="small text-body-secondary">
                <i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>
                <time datetime="{{ $booking->deposit_due_at->toIso8601String() }}">{{ $booking->deposit_due_at->timezone(config('booking.timezone'))->format('d M Y, H:i') }}</time>
                · Amman
            </span>
        @endif
    </div>
    <div class="customer-deposit-price">
        <small class="text-body-secondary">Deposit balance</small>
        <strong><bdi>{{ $booking->deposit_amount === null ? '—' : $booking->remainingDeposit() . ' ' . config('payments.currency') }}</bdi></strong>
    </div>
    <div class="customer-deposit-action">
        @if ($booking->receipt_under_review)
            @include('booking._status', ['state' => 'pending', 'label' => 'Receipt under review', 'icon' => 'fa-clock'])
        @elseif ($booking->deposit_due_at?->isPast())
            @include('booking._status', ['state' => 'expired', 'label' => 'Deadline passed', 'icon' => 'fa-calendar-xmark'])
        @elseif (!$cliqConfigured)
            <a class="btn btn-outline-secondary product-action" href="{{ route('payments.create', ['booking_id' => $booking->id]) }}">
                <i class="fa-regular fa-circle-question" aria-hidden="true"></i> Payment unavailable · Get help
            </a>
        @else
            <a class="btn btn-primary product-action" href="{{ route('payments.create', ['booking_id' => $booking->id]) }}">
                <i class="fa-solid fa-wallet" aria-hidden="true"></i> Pay deposit via CliQ
            </a>
        @endif
    </div>
</li>
