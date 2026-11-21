@extends('layouts.dashboard')

@section('title', 'Booking ' . $booking->id)
@section('page-title', 'Booking ' . $booking->id)
@section('page-icon', 'fa-clipboard-list')
@section('page-description', 'Track your move, furniture inventory, and payment status.')

@php
    $user = auth()->user();
    $owner = $user->id === $booking->customer_id;
    $draft = $owner && $booking->status === 'draft';
    $inventoryComplete = $booking->inventoryItems->contains('status', 'confirmed');
    $locationsAndVehicleComplete = $booking->pickup_latitude !== null
        && $booking->pickup_longitude !== null
        && $booking->destination_latitude !== null
        && $booking->destination_longitude !== null
        && $booking->scheduled_start_at?->isFuture()
        && $booking->scheduled_end_at?->gt($booking->scheduled_start_at)
        && $booking->vehicle_id !== null;
    $currentDraftStep = !$inventoryComplete ? 'inventory' : (!$locationsAndVehicleComplete ? 'locations' : 'review');
@endphp

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route($user->role === 'driver' ? 'driver.bookings.index' : 'booking.index') }}">Back to bookings</a>
@endsection

@section('content')
    <p data-workflow-status class="booking-workflow-message" role="status"></p>
    @if ($draft)
        @include('booking._steps', ['current' => $currentDraftStep, 'line' => true, 'links' => [
            'inventory' => '#inventory',
            'locations' => route('booking.edit', $booking),
            'review' => '#review',
        ]])
    @endif
    @if ($owner)
        @include('booking._journey')
    @endif


    <div class="row g-4">
        <div class="col-lg-8">
            @if ($draft)
                @include('booking._inventory')

            @endif
    <section class="card mb-3" id="locations">
        <div class="card-body">
            @include('booking._status', ['state' => $booking->status])
            <h2 class="h5 workspace-heading mt-2">Move details</h2>
            <p>{{ $booking->scheduled_start_at?->timezone('Asia/Amman')->format('D, d M · H:i') ?? 'Choose a date and time' }}</p>
            @foreach (['pickup' => 'Pickup', 'destination' => 'Destination'] as $stop => $label)
                @if ($booking->{$stop.'_latitude'} !== null && $booking->{$stop.'_longitude'} !== null)
                    <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener"
                       href="https://www.google.com/maps/dir/?api=1&amp;destination={{ $booking->{$stop.'_latitude'} }},{{ $booking->{$stop.'_longitude'} }}">{{ $label }} directions</a>
                @endif
                <p class="small text-body-secondary mt-2 mb-0">{{ $label }} floor {{ $booking->{$stop.'_floor'} }} · {{ $booking->{$stop.'_has_elevator'} ? 'Elevator available' : 'No elevator' }}</p>
            @endforeach
            @if ($booking->notes)<p class="mt-2">{{ $booking->notes }}</p>@endif
            @if ($booking->rejection_reason)<p>{{ $booking->rejection_reason }}</p>@endif
        @if ($owner && in_array($booking->status, ['confirmed', 'in_progress', 'completed'], true) && $booking->driver?->user)
                <div class="customer-driver">
                    <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                    <div><strong>{{ $booking->driver->user->name }}</strong>
                        @if ($booking->driver->user->phone)
                            <a class="d-block small" href="tel:{{ $booking->driver->user->phone }}">{{ $booking->driver->user->phone }}</a>
                        @endif
                    </div>
                </div>
            @endif
            @if ($draft)
                <a class="btn btn-outline-secondary" href="{{ route('booking.edit', $booking) }}">Edit locations, schedule and vehicle</a>
            @endif
        </div>
    </section>

    @if ($draft)
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
    <section class="card mb-3">
            <div class="card-body">
                <h2 class="h5 workspace-heading" id="review">Review and send</h2>
                @if (!\App\Models\PlatformSetting::bookingConfigured())
                    <p class="alert alert-warning mb-0">Quotes are temporarily unavailable. Your draft is saved. <a href="{{ route('info.contact') }}">Contact support</a> for help.</p>
                @else
                <p class="small text-body-secondary">Calculate your quote to see the total and required deposit before sending the request.</p>
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
                    <form id="submit-booking" data-workflow-form method="POST" action="{{ route('bookings.submit', $booking, false) }}" hidden>
                        @csrf
                        <input type="hidden" name="expected_total">
                        <button class="btn btn-primary">Send booking request</button>
                    </form>
                </div>
                @endif
            </div>
        </section>
    @else
        <section class="card card-body mb-3">
            <h2 class="h5">Furniture and services</h2>
            <ul class="mb-0">
                @forelse ($booking->inventoryItems->where('status', 'confirmed') as $item)
                    <li>{{ str($item->item_type)->headline() }} × {{ $item->quantity }}</li>
                @empty
                    <li class="text-body-secondary">No confirmed furniture items.</li>
                @endforelse
                @foreach ($booking->bookingServices as $line)
                    <li>{{ $line->service_name }} × {{ $line->quantity }}</li>
                @endforeach
            </ul>
        </section>
    @endif

        </div>
        <aside class="col-lg-4 d-grid gap-3 align-content-start">
            <div class="card card-body" data-booking-summary>
                <h2 class="h5 workspace-heading">Booking summary</h2>
                <p>@include('booking._status', ['state' => $booking->status])</p>
                <dl class="row mb-0">
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
