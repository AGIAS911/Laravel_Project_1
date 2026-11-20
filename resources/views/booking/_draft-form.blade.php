<form class="move-editor" method="POST" action="{{ route('booking.update', $draft) }}">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Check the highlighted fields.</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 align-items-start">
        <div class="col-lg-8 d-grid gap-3">
            <div id="booking-map-panel" hidden>
                    <fieldset class="move-map-target">
                        <legend class="small text-body-secondary mb-2">Choose a stop, then click or tap the map.</legend>
                        @foreach (['pickup' => 'Pickup', 'destination' => 'Destination'] as $key => $label)
                            <input class="btn-check" type="radio" name="map_target" value="{{ $key }}" id="map-{{ $key }}" @checked($loop->first)>
                            <label class="btn btn-outline-secondary btn-sm" for="map-{{ $key }}">
                                <span class="move-stop-dot move-stop-{{ $key }}" aria-hidden="true"></span>{{ $label }}
                            </label>
                        @endforeach
                    </fieldset>
                    <button class="btn btn-outline-secondary btn-sm mt-2" type="button" data-current-location hidden>
                        <i class="fa-solid fa-crosshairs me-1" aria-hidden="true"></i> Use my location for pickup
                    </button>
                    <div id="booking-map" class="move-editor-map" aria-label="Pickup and destination map"></div>
                    <p class="small text-body-secondary mb-0 mt-2" data-location-status role="status"></p>
                </div>
            <section class="card card-body move-editor-panel" aria-labelledby="move-route-title">
                <h2 class="h5 workspace-heading" id="move-route-title">Pickup and destination</h2>


                <div class="row g-3 mt-1">
                    @foreach (['pickup' => 'Pickup', 'destination' => 'Destination'] as $key => $label)
                        <div class="col-md-6">
                            <fieldset class="move-stop-fields">
                                <legend class="h6 workspace-heading"><span class="move-stop-dot move-stop-{{ $key }}" aria-hidden="true"></span>{{ $label }}</legend>
                                <div class="row g-2">
                                    @foreach (['latitude' => 'Latitude', 'longitude' => 'Longitude', 'floor' => 'Floor'] as $field => $fieldLabel)
                                        @php($name = $key.'_'.$field)
                                        <div class="col-6">
                                            <label class="form-label small" for="{{ $name }}">{{ $fieldLabel }}</label>
                                            <input @class(['form-control', 'is-invalid' => $errors->has($name)]) id="{{ $name }}" name="{{ $name }}"
                                                inputmode="{{ $field === 'floor' ? 'numeric' : 'decimal' }}"
                                                value="{{ old($name, $draft->{$name} ?? ($field === 'floor' ? 0 : '')) }}"
                                                @error($name) aria-describedby="{{ $name }}-error" @enderror>
                                            @error($name)<div id="{{ $name }}-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    @endforeach
                                    @php($name = $key.'_has_elevator')
                                    <div class="col-6">
                                        <label class="form-label small" for="{{ $name }}">Elevator</label>
                                        <select class="form-select" id="{{ $name }}" name="{{ $name }}">
                                            <option value="0" @selected(!old($name, $draft->{$name}))>No</option>
                                            <option value="1" @selected(old($name, $draft->{$name}))>Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    @endforeach
                </div>
                <p class="small text-body-secondary mt-3 mb-0">You can enter coordinates directly. Enter both latitude and longitude for each stop.</p>
            <div class="mt-3">
        <label class="h6 workspace-heading" for="notes">Access notes <span class="small fw-normal text-body-secondary">(optional)</span></label>
        <textarea @class(['form-control', 'is-invalid' => $errors->has('notes')]) id="notes" name="notes" rows="3"
            placeholder="Parking, building access, or other details for the move">{{ old('notes', $draft->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</section>
            <section class="card card-body move-editor-panel" aria-labelledby="move-time-title">
                <h2 class="h5 workspace-heading" id="move-time-title">Booking appointment</h2>
                <div class="col-md-6">
                    <label class="form-label small" for="scheduled_start_at">Booking date and time (Amman)</label>
                    <input @class(['form-control', 'is-invalid' => $errors->has('scheduled_start_at')])
                        type="datetime-local" step="1" id="scheduled_start_at" name="scheduled_start_at"
                        value="{{ old('scheduled_start_at', $draft->scheduled_start_at?->timezone(config('booking.timezone'))->format('Y-m-d\TH:i:s')) }}"
                        @error('scheduled_start_at') aria-describedby="booking-time-error" @enderror>
                    @error('scheduled_start_at')<div id="booking-time-error" class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </section>
        </div>
        <aside class="col-lg-4 d-grid gap-3">
            <section class="card card-body move-editor-panel">
                <h2 class="h5 workspace-heading"><i class="fa-solid fa-route me-1" aria-hidden="true"></i> Route</h2>
                <dl class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                    <dt>Road distance</dt>
                    <dd class="mb-0">{{ $draft->distance_km === null ? 'Calculated at review' : $draft->distance_km . ' km' }}</dd>
                </dl>
                <p class="route-summary-note mb-0">
                    <i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i> Changes to either stop require a new quote before you send the booking.
                </p>
            </section>
            <section class="card card-body move-editor-panel" id="vehicle-selection" aria-labelledby="move-vehicle-title">
                <h2 class="h5 workspace-heading" id="move-vehicle-title">Vehicle</h2>
                <p class="small text-body-secondary">Choose a booking time to check availability.</p>
                @php($selectedVehicle = old('vehicle_id', $draft->vehicle_id))
                <label class="form-label small" for="vehicle_id">Selected vehicle</label>
                <select @class(['form-select', 'is-invalid' => $errors->has('vehicle_id')]) id="vehicle_id" name="vehicle_id">
                    <option value="">Choose later</option>
                    @if ($selectedVehicle)
                        <option value="{{ $selectedVehicle }}" selected>{{ (string) $draft->vehicle_id === (string) $selectedVehicle ? ($draft->vehicle?->name ?? 'Vehicle #'.$selectedVehicle) : 'Vehicle #'.$selectedVehicle }}</option>
                    @endif
                </select>
                @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <button type="button" class="btn btn-outline-secondary btn-sm mt-3 align-self-start" data-find-vehicles="{{ route('bookings.vehicles', [], false) }}">
                    <i class="fa-solid fa-magnifying-glass me-1" aria-hidden="true"></i> Find available vehicles
                </button>
                <p data-vehicle-status class="small mb-0 mt-2" role="status"></p>
                <div class="customer-vehicle-options mt-3" data-vehicle-options></div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-3 align-self-start" data-more-vehicles hidden>Load more vehicles</button>
            </section>
        </aside>
    </div>

    <div class="move-editor-footer">
        <small class="text-body-secondary">You can update these details while the booking is a draft.</small>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i> Save draft</button>
    </div>
</form>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush

<script src="{{ asset('js/booking-workflow.js') }}" defer></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous" defer></script>
<script src="{{ asset('js/booking-map.js') }}" defer></script>
