@extends('layouts.dashboard')

@section('title', 'Booking ' . $booking->id)
@section('page-icon', 'fa-clipboard-list')

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
    $wizardReview = $showReview && $booking->booking_method;
    $servicesAnchor = $wizardReview ? 'moving-services' : 'services';
    $selectedTypes = $showReview ? $booking->bookingServices->pluck('service.type') : collect();
@endphp

@section('page-title', $wizardReview ? 'Review your move' : 'Booking ' . $booking->id)
@if ($wizardReview)
    @section('page-anchor', 'services')
@endif
@section('page-description', $wizardReview ? 'Check the details and your quote before paying the deposit.' : 'Track your move, furniture inventory, and payment status.')

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
    @if ($owner && !$wizardReview)
        @include('booking._journey')
    @endif

    <div @class(['row g-4', 'booking-draft-detail' => $draft, 'booking-review-layout' => $wizardReview])>
        <div class="col-lg-8">
            @if ($draft && !$wizardReview && $booking->needsInventory())
                @include('booking._inventory')
            @endif
    <section class="card mb-3 product-booking-section" id="locations" aria-labelledby="move-details-heading">
        <div class="card-body">
            <h2 class="h5 workspace-heading" id="move-details-heading"><i class="fa-solid fa-route me-1" aria-hidden="true"></i> Move details</h2>
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
                            <h3 class="h6 workspace-heading mb-1">
                                <span class="move-stop-letter move-stop-{{ $stop }} me-1" aria-hidden="true">{{ $loop->first ? 'A' : 'B' }}</span>{{ $stopLabel }}
                            </h3>
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
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-secondary product-action" href="{{ route('booking.edit', $booking->booking_method ? ['booking' => $booking, 'step' => 'route'] : $booking) }}">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $booking->booking_method ? 'Edit locations' : 'Edit locations and appointment' }}
                    </a>
                    @if ($wizardReview)
                        <a class="btn btn-outline-secondary product-action" href="{{ route('booking.edit', ['booking' => $booking, 'step' => 'time']) }}">
                            <i class="fa-regular fa-calendar-days" aria-hidden="true"></i> Edit date and time
                        </a>
                    @endif
                </div>
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
        <section class="card mb-3 product-booking-section product-service-panel" id="{{ $servicesAnchor }}" aria-labelledby="moving-services-heading"
            data-workflow-scope data-workflow-return="{{ $servicesAnchor }}">
            <div class="card-body">
                <h2 class="h5 workspace-heading" id="moving-services-heading">Moving services</h2>
                <p class="small text-body-secondary">Review your selected services or add help for your move.</p>
                <p class="booking-workflow-message" data-workflow-status role="status"></p>
                @foreach ($booking->bookingServices as $line)
                    <div class="product-service-row d-flex flex-wrap align-items-center gap-2 mb-2">
                        <div class="product-service-name">
                            <span class="product-service-icon"><x-service-icon :type="$line->service?->type" /></span>
                            <strong>{{ $line->service_name }}</strong>
                        </div>
                        @if ($line->unit !== 'fixed')
                            <form data-workflow-form data-auto-quantity method="POST" action="{{ route('booking-services.update', $line, false) }}">
                                @csrf @method('PUT')
                                <x-service-quantity :label="$line->service_name . ' quantity'" :value="$line->quantity" />
                            </form>
                        @endif
                        <form data-workflow-form method="POST" action="{{ route('booking-services.destroy', $line, false) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm product-action" aria-label="Remove {{ $line->service_name }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Remove</button>
                        </form>
                    </div>
                @endforeach
                <form class="row g-2 align-items-end" data-workflow-form method="POST" action="{{ route('booking-services.store', [], false) }}">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <div class="col-sm">
                        <label class="form-label" for="service_id">Service</label>
                        <select class="form-select" name="service_id" id="service_id" required>
                            <option value="">Choose an additional service</option>
                            @foreach ($services->whereNotIn('id', $booking->bookingServices->pluck('service_id')) as $service)
                                @php($needsWorkers = $service->type === \App\Models\Service::TYPE_ASSEMBLY && !$selectedTypes->contains(\App\Models\Service::TYPE_WORKERS))
                                @continue($service->type === \App\Models\Service::TYPE_TRANSPORT && $selectedTypes->contains(\App\Models\Service::TYPE_TRANSPORT))
                                <option value="{{ $service->id }}" @disabled($needsWorkers)>{{ $service->name }} · {{ $service->price }} JOD{{ $needsWorkers ? ' (add workers first)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-auto">
                        <label class="form-label" for="service_quantity">Quantity</label>
                        <x-service-quantity id="service_quantity" label="New service quantity" />
                    </div>
                    <div class="col-auto"><button class="btn btn-outline-primary product-action"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add service</button></div>
                </form>
            </div>
        </section>
        @unless ($wizardReview)
            @include('booking._quote')
        @endunless
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
            <div class="card card-body booking-detail-summary product-booking-summary" data-booking-summary>
                <h2 class="h5 workspace-heading"><i class="fa-solid fa-receipt me-1" aria-hidden="true"></i> Booking summary</h2>
                <p>@include('booking._status', ['state' => $booking->status])</p>
                <dl class="row mb-0" @if ($wizardReview) aria-live="polite" @endif>
                    @if ($owner && ($booking->preferredVehicleType || $booking->vehicle?->vehicleType))
                        <dt class="col-6">Vehicle type</dt>
                        <dd class="col-6">{{ $booking->preferredVehicleType?->name ?? $booking->vehicle->vehicleType?->name }}</dd>
                    @endif

                    <dt class="col-6">Distance</dt><dd class="col-6" data-summary-distance>{{ $booking->distance_km !== null ? $booking->distance_km . ' km' : ($wizardReview ? 'After quote' : '—') }}</dd>
                    <dt class="col-6">Total</dt><dd class="col-6" data-summary-total>{{ $booking->total_price !== null ? $booking->total_price . ' JOD' : ($wizardReview ? 'After quote' : '—') }}</dd>
                    <dt class="col-6">Deposit</dt><dd class="col-6" data-summary-deposit>{{ $booking->deposit_amount !== null ? $booking->deposit_amount . ' JOD' : ($wizardReview ? 'After quote' : '—') }}</dd>
                </dl>
                @if ($wizardReview)
                    @include('booking._quote')
                @endif
            </div>

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
                        <a href="#{{ $servicesAnchor }}"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i> Services</a>
                        <a href="#review"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Review and send</a>
                    @endif
                </nav>
            @endif

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
        @if ($wizardReview && $booking->needsInventory())
            <div class="col-lg-8">
                @include('booking._inventory')
            </div>
        @endif
    </div>
    <script src="{{ asset('js/booking-workflow.js') }}" defer></script>
@endsection
