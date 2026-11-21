@php
    $selectedServices = $booking->bookingServices->pluck('service');
    $serviceTypes = $selectedServices->pluck('type');
    $servicesReady = $selectedServices->every(fn ($service) => $service?->is_active)
        && $serviceTypes->filter(fn ($type) => $type === \App\Models\Service::TYPE_TRANSPORT)->count() === 1
        && (!$serviceTypes->contains(\App\Models\Service::TYPE_ASSEMBLY) || $serviceTypes->contains(\App\Models\Service::TYPE_WORKERS));

    $steps = [
        ['Confirm furniture', '#inventory', $booking->inventoryItems->contains('status', 'confirmed')],
        ['Set route and time', route('booking.edit', $booking), $booking->pickup_latitude !== null && $booking->pickup_longitude !== null
            && $booking->destination_latitude !== null && $booking->destination_longitude !== null
            && $booking->scheduled_start_at?->isFuture()],
        ['Choose a vehicle', route('booking.edit', $booking).'#vehicle-selection', $booking->vehicle_id !== null],
        ['Select moving services', '#services', $servicesReady],
    ];
    $next = collect($steps)->first(fn ($step) => !$step[2]);
    $paymentUrl = $receiptUnderReview ? route('payments.index') : route('payments.create', ['booking_id' => $booking->id]);
    [$heading, $description, $link, $action] = match ($booking->status) {
        'draft' => $next
            ? [$next[0], 'Complete the remaining booking details to calculate your quote.', $next[1], 'Continue']
            : ['Review your quote', 'Check the price and deposit before sending your booking request.', '#review', 'Go to review'],
        'pending' => ['Waiting for the driver', 'Your request has been sent. Check back here for the driver’s response.', null, null],
        'awaiting_deposit' => $receiptUnderReview
            ? ['Receipt under review', 'Your receipt has been uploaded. We’ll confirm the booking after verifying the transfer.', $paymentUrl, 'View payment status']
            : ($booking->deposit_due_at?->isPast()
                ? ['Deposit deadline passed', 'Open your payment history to check your deposit status.', route('payments.index'), 'View payments']
                : ($cliqConfigured
                    ? ['Pay your deposit', 'Your driver accepted. Transfer the deposit via CliQ and upload your receipt.', $paymentUrl, 'Pay deposit']
                    : ['Deposit payment unavailable', 'Your driver accepted. Contact support for help with the deposit.', $paymentUrl, 'Get help'])),
        'confirmed' => ['Your move is confirmed', 'Check the schedule and keep your driver’s contact details handy.', '#locations', 'View move details'],
        'in_progress' => ['Your move is in progress', 'Your driver has started the move.', '#locations', 'View move details'],
        'completed' => ['Your move is complete', 'Share feedback about your experience with the driver.', '#booking-review', $booking->review ? 'Edit review' : 'Rate your move'],
        'rejected' => ['The driver declined this request', 'You can start a new booking and choose another vehicle.', route('booking.create'), 'Start a new booking'],
        'cancelled' => ['This booking was cancelled', 'Start a new booking when you are ready to move.', route('booking.create'), 'Start a new booking'],
        default => [str($booking->status)->headline(), '', null, null],
    };
@endphp

<section class="booking-journey mb-4" aria-label="Next step">
    <div class="booking-journey-header">
        <div>
            <p class="booking-journey-label">Next step</p>
            <h2 class="h4 workspace-heading mb-1">{{ $heading }}</h2>
            @if ($description)<p class="text-body-secondary mb-0">{{ $description }}</p>@endif
        </div>
        @if ($link)
            <a class="btn btn-primary" href="{{ $link }}">{{ $action }} <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a>
        @endif
    </div>
    @if ($draft)
        <ul class="booking-checklist list-unstyled mb-0" aria-label="Draft checklist">
            @foreach ($steps as [$label, $url, $complete])
                <li>
                    <a href="{{ $url }}">
                        <i @class(['fa-solid', 'fa-circle-check text-success' => $complete, 'fa-circle-dot text-body-secondary' => !$complete]) aria-hidden="true"></i>
                        {{ $label }}<span class="visually-hidden">{{ $complete ? ' (complete)' : ' (to do)' }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
