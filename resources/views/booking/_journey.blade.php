@php
    $selectedServices = $booking->bookingServices->pluck('service');
    $serviceTypes = $selectedServices->pluck('type');
    $servicesReady = $selectedServices->every(fn ($service) => $service?->is_active)
        && $serviceTypes->filter(fn ($type) => $type === \App\Models\Service::TYPE_TRANSPORT)->count() === 1
        && (!$serviceTypes->contains(\App\Models\Service::TYPE_ASSEMBLY) || $serviceTypes->contains(\App\Models\Service::TYPE_WORKERS));

    $steps = [
        ['Confirm furniture', '#inventory', $hasConfirmedItems],
        ['Set route and time', route('booking.edit', $booking), $hasRouteAndTime],
        ['Choose vehicle type', route('booking.edit', $booking).'#vehicle-type-heading', (bool) ($booking->preferredVehicleType?->is_active || $booking->vehicle?->vehicleType?->is_active)],
        ['Select moving services', '#services', $servicesReady],
    ];
    if ($booking->booking_method) {
        $editStep = fn ($step) => route('booking.edit', ['booking' => $booking, 'step' => $step]);
        $steps = [
            ...($booking->needsInventory() ? [['Confirm furniture', '#inventory', $hasConfirmedItems]] : []),
            ['Choose vehicle size', $editStep('type'), $booking->preferredVehicleType?->is_active],
            ['Set pickup and destination', $editStep('route'), $booking->hasRoute()],
            ['Choose date and time', $editStep('time'), $booking->scheduled_start_at?->isFuture()],

            ['Select moving services', '#services', $servicesReady],
        ];
    }
    $needsItems = $booking->needsInventory() && !$hasConfirmedItems;
    $next = collect($steps)->first(fn ($step) => !$step[2]);
    $paymentUrl = $receiptUnderReview ? route('payments.index') : route('payments.create', ['booking_id' => $booking->id]);
    $paidAndSearching = $booking->status === 'pending' && $booking->deposit_verified_at;
    $activeOffer = $booking->driver_id && $booking->driver_offer_expires_at?->isFuture();
    [$heading, $description, $link, $action] = match ($booking->status) {
        'draft' => $next
            ? [$next[0], $needsItems ? 'Add at least one item or confirm a suggestion from your scan.' : 'Continue your saved draft to review the price.', $next[1], $needsItems ? 'Go to inventory' : 'Continue']
            : ['Review your quote', 'Check the price and deposit before sending your booking request.', '#review', 'Go to review'],
        'pending' => $paidAndSearching
            ? match (true) {
                !$booking->scheduled_start_at?->isFuture() => ['Appointment needs attention', 'Your deposit is verified. Contact support to reschedule or discuss a refund.', route('info.contact'), 'Contact support'],
                $activeOffer => ['Waiting for driver confirmation', 'Your deposit is verified and we sent the request to a driver. Details will appear when they accept.', null, null],
                default => ['Finding a driver', 'Your deposit is verified. We are looking for an available driver and vehicle for this appointment.', null, null],
            }
            : ['Waiting for the driver', 'Your request has been sent. Check back here for the driver’s response.', null, null],
        'awaiting_deposit' => $receiptUnderReview
            ? ['Receipt under review', 'We are reviewing your receipt. Once the full deposit is verified, you can track driver confirmation here.', $paymentUrl, 'View payment status']
            : ($booking->deposit_due_at?->isPast()
                ? ['Deposit deadline passed', 'Contact support if you transferred any part of your deposit.', route('info.contact'), 'Contact support']
                : ($cliqConfigured
                    ? ['Pay your deposit', 'Transfer the deposit via CliQ and upload your receipt. We will look for a driver once the full deposit is verified.', $paymentUrl, 'Pay deposit via CliQ']
                    : ['Deposit payment unavailable', 'Contact support for help with the deposit.', $paymentUrl, 'Get help'])),
        'confirmed' => ['Your move is confirmed', 'Your deposit is verified. Your assigned vehicle and driver details are ready.', '#assignment', 'View vehicle and driver'],
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
        @elseif ($paidAndSearching && !$activeOffer && $booking->scheduled_start_at?->isFuture())
            <form method="POST" action="{{ route('bookings.match-driver', $booking) }}">
                @csrf
                <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-arrows-rotate me-1" aria-hidden="true"></i> Check availability</button>
            </form>
        @endif
    </div>
    @if ($draft)
        <ul class="booking-checklist list-unstyled mb-0" aria-label="Draft checklist">
            @foreach ($steps as [$label, $url, $complete])
                <li>
                    @php($canOpen = !$booking->booking_method || $complete || $label === ($next[0] ?? null))
                    @if ($canOpen)<a href="{{ $url }}">@else<span class="booking-checklist-inactive">@endif
                        <i @class(['fa-solid', 'fa-circle-check text-success' => $complete, 'fa-circle-dot text-body-secondary' => !$complete]) aria-hidden="true"></i>
                        {{ $label }}<span class="visually-hidden">{{ $complete ? ' (complete)' : ' (to do)' }}</span>
                    @if ($canOpen)</a>@else</span>@endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
