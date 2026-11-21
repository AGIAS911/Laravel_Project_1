@extends('layouts.dashboard')

@section('title', 'Booking ' . $booking->id)
@section('page-title', 'Booking ' . $booking->id)
@section('page-icon', 'fa-clipboard-list')
@section('page-description', 'Track your move, furniture inventory, and payment status.')

@php
    $user = auth()->user();
    $owner = $user->id === $booking->customer_id;
    $draft = $owner && $booking->status === 'draft';
    $hasConfirmedItems = $booking->inventoryItems->contains('status', 'confirmed');
    $hasRouteAndTime = $booking->hasRoute() && $booking->scheduled_start_at?->isFuture();
    $wizardStep = match (true) {
        $booking->needsInventory() && !$hasConfirmedItems => 'inventory',
        !$booking->preferredVehicleType?->is_active => 'type',
        !$booking->hasRoute() => 'route',
        !$hasRouteAndTime => 'time',

        default => 'review',
    };
    $showReview = $draft && (!$booking->booking_method || $wizardStep === 'review');
    $checkoutReady = $showReview && \App\Models\PlatformSetting::bookingConfigured();
@endphp

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route($user->role === 'driver' ? 'driver.bookings.index' : 'booking.index') }}">Back to bookings</a>
@endsection

@section('content')
    <p data-workflow-status class="booking-workflow-message" role="status"></p>
    @if ($draft && $booking->booking_method)
        @include('booking._wizard-steps', ['current' => $wizardStep])
    @elseif ($draft)
        @include('booking._steps', ['current' => !$hasConfirmedItems ? 'inventory' : (!$hasRouteAndTime || !($booking->preferred_vehicle_type_id || $booking->vehicle_id) ? 'locations' : 'review'), 'line' => true, 'links' => [
            'inventory' => '#inventory',
            'locations' => route('booking.edit', $booking),
            'review' => '#review',
        ]])
    @endif
    @if ($owner)
        @include('booking._journey')
    @endif


    <div @class(['row g-4', 'booking-draft-detail' => $draft])>
        <div class="col-lg-8">
            @if ($draft && $booking->needsInventory())
                @include('booking._inventory')

            @endif
    <section class="card mb-3" id="locations">
        <div class="card-body">
            <h2 class="h5 workspace-heading"><i class="fa-solid fa-route me-1" aria-hidden="true"></i> Move details</h2>
            <p class="text-body-secondary mb-3">
                <i class="fa-regular fa-calendar-days me-1" aria-hidden="true"></i>
                @if ($booking->scheduled_start_at)
                    @php($appointment = $booking->scheduled_start_at->timezone(config('booking.timezone')))
                    <time datetime="{{ $appointment->toIso8601String() }}">{{ $appointment->format('D, d M · H:i') }}</time> (Amman)
                @else
                    Date and time to be set
                @endif
            </p>
            <div class="row g-2 mb-3">
                @foreach (['pickup' => 'Pickup', 'destination' => 'Destination'] as $stop => $stopLabel)
                    <div class="col-sm-6">
                        <div class="h-100 border rounded-3 p-3">
                            <h3 class="h6 workspace-heading mb-1"><i class="fa-solid {{ $stop === 'pickup' ? 'fa-circle-dot' : 'fa-location-dot' }} me-1" aria-hidden="true"></i> {{ $stopLabel }}</h3>
                            @if ($booking->{$stop.'_latitude'} !== null && $booking->{$stop.'_longitude'} !== null)
                                <p class="small text-body-secondary mb-1">Floor {{ $booking->{$stop.'_floor'} }} · {{ $booking->{$stop.'_has_elevator'} ? 'Elevator available' : 'No elevator' }}</p>
                                <a class="link-secondary small d-inline-flex align-items-center py-2" target="_blank" rel="noopener"
                                   href="https://www.google.com/maps/dir/?api=1&amp;destination={{ $booking->{$stop.'_latitude'} }},{{ $booking->{$stop.'_longitude'} }}">{{ $stopLabel }} directions <i class="fa-solid fa-arrow-up-right-from-square ms-1" aria-hidden="true"></i></a>
                            @else
                                <p class="small text-body-secondary mb-0">Location to be set</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($booking->notes)<p class="mt-2">{{ $booking->notes }}</p>@endif
            @if ($booking->rejection_reason)<p>{{ $booking->rejection_reason }}</p>@endif
            @if ($draft)
                <a class="btn btn-outline-secondary" href="{{ route('booking.edit', $booking->booking_method ? ['booking' => $booking, 'step' => 'route'] : $booking) }}">Edit locations and appointment</a>
            @endif
        </div>
    </section>

    @if ($owner && $booking->assignmentVisibleToCustomer())
        <section class="card card-body mb-3" id="assignment" aria-labelledby="assignment-title">
            <h2 class="h5 workspace-heading mb-3" id="assignment-title">Assigned vehicle and driver</h2>
            <div class="row g-3">
                @if ($booking->vehicle)
                    <div class="col-md-6">
                        <div class="border rounded-3 h-100 p-3">
                            <h3 class="h6 workspace-heading"><i class="fa-solid fa-truck me-1" aria-hidden="true"></i> Vehicle</h3>
                            @if ($booking->vehicle->image)
                                <img src="{{ asset($booking->vehicle->image) }}" class="booking-assignment-photo mb-2" alt="" width="120" height="72">
                            @endif
                            <p class="fw-semibold mb-1">{{ $booking->vehicle->name }}</p>
                            <p class="small text-body-secondary mb-0">{{ $booking->vehicle->vehicleType?->name }} · Plate <bdi>{{ $booking->vehicle->plate_number }}</bdi></p>
                        </div>
                    </div>
                @endif
                @if ($booking->driver?->user)
                    <div class="col-md-6">
                        <div class="border rounded-3 h-100 p-3">
                            <h3 class="h6 workspace-heading"><i class="fa-regular fa-id-card me-1" aria-hidden="true"></i> Driver</h3>
                            <p class="fw-semibold mb-1">{{ $booking->driver->user->name }}</p>
                            @if ($booking->driver->user->phone)
                                <a class="btn btn-outline-secondary btn-sm mt-2" href="tel:{{ $booking->driver->user->phone }}">
                                    <i class="fa-solid fa-phone me-1" aria-hidden="true"></i> {{ $booking->driver->user->phone }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
                @if (!$booking->vehicle && !$booking->driver?->user)
                    <p class="text-body-secondary mb-0">Assignment details are unavailable. <a href="{{ route('info.contact') }}">Contact support</a>.</p>
                @endif
            </div>
        </section>
    @endif

    @if ($showReview)
        <section class="card mb-3">
            <div class="card-body">
                <h2 class="h5 workspace-heading" id="services">Services</h2>
                @foreach ($booking->bookingServices as $line)
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span>{{ $line->service_name }}</span>
                        @if ($line->unit !== 'fixed')
                            <form class="d-flex gap-2" data-workflow-form method="POST" action="{{ route('booking-services.update', $line, false) }}">
                                @csrf @method('PUT')
                                <input class="form-control form-control-sm" style="max-width:6rem" name="quantity" aria-label="{{ $line->service_name }} quantity" type="number" min="1" max="1000" value="{{ $line->quantity }}" required>
                                <button class="btn btn-outline-primary btn-sm">Update</button>
                            </form>
                        @endif
                        <form data-workflow-form method="POST" action="{{ route('booking-services.destroy', $line, false) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">Remove</button>
                        </form>
                    </div>
                @endforeach
                <form class="row g-2 align-items-end" data-workflow-form method="POST" action="{{ route('booking-services.store', [], false) }}">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <div class="col-sm">
                        <label class="form-label" for="service_id">Service</label>
                        <select class="form-select" name="service_id" id="service_id" required>
                            <option value="">Choose a service</option>
                            @foreach ($services->whereNotIn('id', $booking->bookingServices->pluck('service_id')) as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} · {{ $service->price }} JOD</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label" for="service_quantity">Quantity</label>
                        <input class="form-control" id="service_quantity" name="quantity" type="number" min="1" max="1000" value="1" required>
                    </div>
                    <div class="col-auto"><button class="btn btn-outline-primary">Add service</button></div>
                </form>
            </div>
        </section>
    <section class="card mb-3" data-workflow-scope>
            <div class="card-body">
                <h2 class="h5 workspace-heading" id="review">Review and send</h2>
                @if (!\App\Models\PlatformSetting::quoteConfigured())
                    <p class="alert alert-warning mb-0">Route pricing is temporarily unavailable. Your draft is saved. <a href="{{ route('info.contact') }}">Contact support</a> for help.</p>
                @else

                @if ($checkoutReady)
                    <p class="small text-body-secondary">Calculate your quote to see the total and deposit. We will look for a driver once your full deposit is verified.</p>
                @else
                    <p class="alert alert-warning">You can calculate the moving price now. CliQ checkout is temporarily unavailable, and your draft is saved.</p>
                @endif
                <p class="booking-workflow-message" data-workflow-status role="status"></p>
                <div class="booking-quote mb-3" data-booking-quote hidden aria-live="polite">
                    <div><span>Road distance</span><strong data-quote-distance></strong></div>
                    <div><span>Total</span><strong data-quote-total></strong></div>
                    <div><span>Deposit</span><strong data-quote-deposit></strong></div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <form data-workflow-form="quote" method="POST" action="{{ route('bookings.quote', $booking, false) }}">
                        @csrf
                        <button class="btn btn-outline-primary">Calculate quote</button>
                    </form>
                    @if ($checkoutReady)
                        <form id="submit-booking" data-workflow-form method="POST" action="{{ route('bookings.submit', $booking, false) }}" hidden>
                            @csrf
                            <input type="hidden" name="expected_total">
                            <button class="btn btn-primary">Continue to CliQ payment</button>
                        </form>
                    @endif
                </div>
                @endif
            </div>
        </section>
    @elseif (!$draft)
        <section class="card card-body mb-3">
            <h2 class="h5">{{ $booking->needsInventory() ? 'Furniture and services' : 'Moving services' }}</h2>
            <ul class="mb-0">
                @forelse ($booking->inventoryItems->where('status', 'confirmed') as $item)
                    <li>{{ config('inventory.items')[$item->item_type]['label'] ?? str($item->item_type)->headline() }} × {{ $item->quantity }}</li>
                @empty
                    @if ($booking->needsInventory())
                        <li class="text-body-secondary">No confirmed furniture items.</li>
                    @endif
                @endforelse
                @foreach ($booking->bookingServices as $line)
                    <li>{{ $line->service_name }} × {{ $line->quantity }}</li>
                @endforeach
            </ul>
        </section>
    @endif

        </div>
        <aside class="col-lg-4 d-grid gap-3 align-content-start">
            @if ($draft)
                <nav class="card card-body booking-detail-links" aria-label="On this booking">
                    <h2 class="h6 workspace-heading">On this booking</h2>
                    @if ($booking->needsInventory())
                        <a href="#inventory"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i> Furniture inventory</a>
                    @endif
                    @if ($booking->preferredVehicleType)
                        <a href="{{ route('booking.edit', ['booking' => $booking, 'step' => 'type']) }}"><i class="fa-solid fa-truck" aria-hidden="true"></i> {{ $booking->preferredVehicleType->name }}</a>
                    @endif
                    <a href="#locations"><i class="fa-solid fa-route" aria-hidden="true"></i> Move details</a>
                    @if (!$booking->booking_method || $wizardStep === 'review')
                        <a href="#services"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i> Services</a>
                        <a href="#review"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Review and send</a>
                    @endif
                </nav>
            @endif
            <div class="card card-body booking-detail-summary" data-booking-summary>
                <h2 class="h5 workspace-heading"><i class="fa-solid fa-receipt me-1" aria-hidden="true"></i> Booking summary</h2>
                <p>@include('booking._status', ['state' => $booking->status])</p>
                <dl class="row mb-0">
                    @if ($owner && ($booking->preferredVehicleType || $booking->vehicle?->vehicleType))
                        <dt class="col-6">Vehicle type</dt>
                        <dd class="col-6">{{ $booking->preferredVehicleType?->name ?? $booking->vehicle->vehicleType?->name }}</dd>
                    @endif

                    <dt class="col-6">Distance</dt><dd class="col-6" data-summary-distance>{{ $booking->distance_km !== null ? $booking->distance_km . ' km' : '—' }}</dd>
                    <dt class="col-6">Total</dt><dd class="col-6" data-summary-total>{{ $booking->total_price !== null ? $booking->total_price . ' JOD' : '—' }}</dd>
                    <dt class="col-6">Deposit</dt><dd class="col-6" data-summary-deposit>{{ $booking->deposit_amount !== null ? $booking->deposit_amount . ' JOD' : '—' }}</dd>
                </dl>
            </div>

    @if ($cancellationRequiresReview)
        <p class="alert alert-info">Contact <a href="{{ route('info.contact') }}">support</a> if you need to cancel. Your payment proof requires a review before cancellation.</p>
    @elseif ($owner && in_array($booking->status, ['pending', 'awaiting_deposit'], true))

                <details class="card card-body">
            <summary class="text-danger fw-semibold">Cancel this booking</summary>
        <form class="mt-2" data-workflow-form method="POST" action="{{ route('bookings.cancel', $booking, false) }}">
            @csrf @method('PATCH')
            <button class="btn btn-outline-danger">Confirm cancellation</button>
        </form>
        </details>
    @endif
    @if ($owner && $booking->status === 'completed')
        @if ($booking->review)
            <section class="card card-body mb-3" id="booking-review">
                <h2 class="h5">Your rating</h2>
                <div class="booking-rating booking-rating-readonly" role="img"
                    aria-label="Your rating: {{ $booking->review->rating }} out of 5 stars">
                    @for ($rating = 1; $rating <= 5; $rating++)
                        <i class="fa-solid fa-star {{ $rating <= $booking->review->rating ? 'is-filled' : 'is-empty' }}" aria-hidden="true"></i>
                    @endfor
                </div>
                <p class="small text-body-secondary mt-2 mb-0">Your rating is saved and cannot be changed.</p>
                @if ($booking->review->comment)
                    <p class="mt-2 mb-0">{{ $booking->review->comment }}</p>
                @endif
            </section>
        @else
        <form class="card card-body" id="booking-review" data-workflow-form method="POST" action="{{ route('booking.reviews.store', $booking, false) }}">
            @csrf
            <h2 class="h5">Rate your move</h2>
            <fieldset class="mb-2">
                <legend class="form-label">Rating</legend>
                <div class="booking-rating" role="radiogroup" aria-label="Rating">
                    @for ($rating = 5; $rating >= 1; $rating--)
                        <input class="visually-hidden" type="radio" id="rating-{{ $rating }}" name="rating"
                            value="{{ $rating }}" required
                            @checked((int) old('rating', $booking->review?->rating) === $rating)>
                        <label for="rating-{{ $rating }}" aria-label="{{ $rating }} out of 5 stars">
                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                        </label>
                    @endfor
                </div>
                @error('rating')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </fieldset>
            <label class="form-label" for="comment">Comment</label>
            <textarea class="form-control mb-2" id="comment" name="comment" maxlength="2000">{{ $booking->review?->comment }}</textarea>
            <div><button class="btn btn-primary">Save review</button></div>
        </form>
        @endif
    @endif
    @if ($user->role === 'driver')
        @include('driver._booking-actions')
    @endif
        </aside>
    </div>
    <script src="{{ asset('js/booking-workflow.js') }}" defer></script>
@endsection
