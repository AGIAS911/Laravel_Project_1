<form class="move-editor" method="POST" action="{{ route('booking.update', $draft) }}">
    @csrf
    @method('PUT')
    @if ($step)
        <input type="hidden" name="booking_step" value="{{ $step }}">
    @endif

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

        <div @class(['col-lg-8' => !$step, 'col-lg-10' => $step, 'd-grid gap-3'])>
            @if ($step !== 'time')
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

                                @foreach (['latitude', 'longitude'] as $field)
                                    @php($name = $key.'_'.$field)
                                    <input type="hidden" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $draft->{$name}) }}">
                                @endforeach

                                <div class="row g-2">
                                    @if ($errors->has($key.'_latitude') || $errors->has($key.'_longitude'))
                                        <div class="col-12 text-danger small" id="{{ $key }}_latitude-error">Please choose the {{ strtolower($label) }} on the map.</div>
                                    @endif

                                    @php($name = $key.'_floor')
                                    <div class="col-6">
                                        <label class="form-label small" for="{{ $name }}">Floor</label>
                                        <input @class(['form-control', 'is-invalid' => $errors->has($name)]) id="{{ $name }}" name="{{ $name }}"
                                            inputmode="numeric"
                                            value="{{ old($name, $draft->{$name} ?? 0) }}"
                                            @error($name) aria-describedby="{{ $name }}-error" @enderror>
                                        @error($name)<div id="{{ $name }}-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

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

                <p class="small text-body-secondary mt-3 mb-0">Choose the pickup and destination on the map above.</p>

                <div class="mt-3">
                    <label class="h6 workspace-heading" for="notes">Access notes <span class="small fw-normal text-body-secondary">(optional)</span></label>
                    <textarea @class(['form-control', 'is-invalid' => $errors->has('notes')]) id="notes" name="notes" rows="3"
                        placeholder="Parking, building access, or other details for the move">{{ old('notes', $draft->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </section>

            @endif
            @if ($step !== 'route')
                <section class="card card-body move-editor-panel" aria-labelledby="move-time-title">
                    <h2 class="h5 workspace-heading" id="move-time-title">Booking appointment</h2>
                    <div class="col-md-6">
                        <label class="form-label" for="scheduled_start_at">Date and time (Amman)</label>
                        <input @class(['form-control', 'is-invalid' => $errors->has('scheduled_start_at')]) type="datetime-local"
                            name="scheduled_start_at" id="scheduled_start_at" step="1" @required($step === 'time')
                            value="{{ old('scheduled_start_at', $draft->scheduled_start_at?->timezone(config('booking.timezone'))->format('Y-m-d\TH:i:s')) }}"
                            @error('scheduled_start_at') aria-describedby="appointment-error" @enderror>
                        @error('scheduled_start_at')<p class="invalid-feedback" id="appointment-error">{{ $message }}</p>@enderror
                    </div>
                </section>
            @endif
        </div>
        @unless ($step)
            <aside class="col-lg-4">
                <section class="card card-body move-editor-panel" aria-labelledby="vehicle-type-heading">
                    <h2 class="h5 workspace-heading" id="vehicle-type-heading">Vehicle type</h2>
                    <p class="small text-body-secondary">MoveIt assigns the vehicle and driver for your appointment.</p>
                    <label class="form-label" for="preferred_vehicle_type_id">Preferred type</label>
                    <select class="form-select" id="preferred_vehicle_type_id" name="preferred_vehicle_type_id">
                        <option value="">Choose a type</option>
                        @foreach ($vehicleTypes as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('preferred_vehicle_type_id', $draft->preferred_vehicle_type_id ?? $draft->vehicle?->vehicle_type_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </section>
            </aside>
        @endunless
    </div>

    <div class="move-editor-footer">
        <small class="text-body-secondary">You can update these details while the booking is a draft.</small>
        <button class="btn btn-primary" type="submit">
            {{ ['route' => 'Continue to date and time', 'time' => 'Continue to review'][$step] ?? 'Save draft' }}
            <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
        </button>
    </div>
</form>

<script src="{{ asset('js/booking-workflow.js') }}" defer></script>
@if (!$step || $step === 'route')
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous" defer></script>
<script src="{{ asset('js/booking-map.js') }}" defer></script>
@endif
