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

        <div @class(['col-lg-8' => !$step, 'col-12' => $step !== null, 'd-grid gap-3'])>
            @if ($step !== 'time')
                <div @class(['move-location-layout', 'move-location-layout-split' => $step === 'route'])>
                    <section class="card move-location-map-card" aria-labelledby="move-map-title">
                        <div class="move-location-map-header">
                            <h2 class="h5 workspace-heading" id="move-map-title">Set your pins</h2>
                            <p class="small text-body-secondary mb-0" data-map-unavailable>The map is unavailable. Reload this page to try again.</p>
                            <p class="small text-body-secondary mb-0" id="move-map-hint" data-map-hint role="status" hidden></p>
                        </div>
                        <div id="booking-map-panel" hidden>
                            <fieldset class="move-map-target">
                                <legend class="visually-hidden">Choose which pin to place</legend>
                                @foreach (['pickup' => 'Pickup', 'destination' => 'Destination'] as $key => $label)
                                    <input class="btn-check" type="radio" name="map_target" value="{{ $key }}" id="map-{{ $key }}" @checked($loop->first)>
                                    <label class="btn btn-outline-secondary btn-sm" for="map-{{ $key }}">
                                        <span class="move-stop-letter move-stop-{{ $key }}" aria-hidden="true">{{ $loop->first ? 'A' : 'B' }}</span>{{ $label }}
                                    </label>
                                @endforeach
                            </fieldset>
                            <div class="move-location-map-frame">
                                <div id="booking-map" class="move-editor-map" role="region" aria-label="Pickup and destination map" aria-describedby="move-map-hint"></div>
                                <i class="fa-solid fa-crosshairs move-location-crosshair" aria-hidden="true"></i>
                            </div>
                            <div class="move-location-map-tools">
                                <button class="btn btn-outline-secondary product-action" type="button" data-pin-center>
                                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i> Pin map center
                                </button>
                                <button class="btn btn-outline-secondary product-action" type="button" data-current-location hidden>
                                    <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Use my location for pickup
                                </button>
                                <p class="small text-body-secondary mb-0" data-location-status role="status"></p>
                                <p class="small fw-semibold mb-0" data-route-info role="status" hidden></p>
                            </div>
                        </div>
                    </section>

                    <section class="card card-body move-editor-panel product-booking-section" aria-labelledby="move-route-title">
                        <h2 class="h5 workspace-heading" id="move-route-title">Stop details</h2>
                        <p class="small text-body-secondary mb-3">Tell your movers how to access each address.</p>

                        <div class="move-location-stops">
                            @foreach (['pickup' => 'Pickup', 'destination' => 'Destination'] as $key => $label)
                                <fieldset class="move-stop-fields">
                                    <legend class="h6 workspace-heading"><span class="move-stop-letter move-stop-{{ $key }}" aria-hidden="true">{{ $loop->first ? 'A' : 'B' }}</span>{{ $label }}</legend>
                                    <p class="move-stop-state small mb-2" data-pin-status="{{ $key }}">
                                        {{ old($key.'_latitude', $draft->{$key.'_latitude'}) !== null && old($key.'_longitude', $draft->{$key.'_longitude'}) !== null ? 'Pin placed' : 'Pin needed' }}
                                    </p>

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
                                            @php($floors = ['0', '1', '2', '3', '4'])
                                            @php($selectedFloor = (string) old($name, $draft->{$name} ?? 0))
                                            <select @class(['form-select', 'is-invalid' => $errors->has($name)]) id="{{ $name }}" name="{{ $name }}" required
                                                @error($name) aria-describedby="{{ $name }}-error" @enderror>
                                                @unless (in_array($selectedFloor, $floors, true))
                                                    <option value="" selected disabled>Choose a floor</option>
                                                @endunless
                                                @foreach ($floors as $floor)
                                                    <option value="{{ $floor }}" @selected($selectedFloor === $floor)>{{ $floor }}</option>
                                                @endforeach
                                            </select>
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
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <label class="h6 workspace-heading" for="notes">Access notes <span class="small fw-normal text-body-secondary">(optional)</span></label>
                            <textarea @class(['form-control', 'is-invalid' => $errors->has('notes')]) id="notes" name="notes" rows="3"
                                placeholder="Parking, building access, or other details for the move">{{ old('notes', $draft->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </section>
                </div>
            @endif
            @if ($step !== 'route')
                <div @class(['move-appointment-layout', 'move-appointment-layout-split' => $step === 'time'])>
                    <section class="card card-body move-editor-panel product-booking-section move-appointment-card" aria-labelledby="move-time-title">
                        <span class="move-appointment-icon" aria-hidden="true"><i class="fa-regular fa-calendar-days"></i></span>
                        <h2 class="h5 workspace-heading" id="move-time-title">Choose your pickup time</h2>
                        <div class="move-appointment-field">
                            <label class="form-label" for="scheduled_start_at">Date and time (Amman)</label>
                            <input @class(['form-control', 'is-invalid' => $errors->has('scheduled_start_at')]) type="datetime-local"
                                name="scheduled_start_at" id="scheduled_start_at" step="{{ $step === 'time' ? 60 : 1 }}" @required($step === 'time')
                                @if ($step === 'time') min="{{ now(config('booking.timezone'))->startOfMinute()->addMinute()->format('Y-m-d\TH:i') }}" @endif
                                value="{{ old('scheduled_start_at', $draft->scheduled_start_at?->timezone(config('booking.timezone'))->format($step === 'time' ? 'Y-m-d\TH:i' : 'Y-m-d\TH:i:s')) }}"
                                aria-describedby="appointment-hint{{ $errors->has('scheduled_start_at') ? ' appointment-error' : '' }}">
                            @error('scheduled_start_at')<p class="invalid-feedback" id="appointment-error">{{ $message }}</p>@enderror
                            <p class="small text-body-secondary mt-2 mb-0" id="appointment-hint"><i class="fa-regular fa-clock me-1" aria-hidden="true"></i> Choose a future time in Amman.</p>
                        </div>
                    </section>

                    @if ($step === 'time')
                        <aside class="card card-body move-editor-panel move-appointment-summary" aria-labelledby="appointment-summary">
                            <h2 class="h5 workspace-heading" id="appointment-summary">Your move so far</h2>
                            @if ($draft->preferredVehicleType)
                                <div class="move-appointment-vehicle">
                                    <x-vehicle-illustration :name="$draft->preferredVehicleType->name" :photo="$draft->preferredVehicleType->image" />
                                    <div><span class="small text-body-secondary">Vehicle size</span><br><strong>{{ $draft->preferredVehicleType->name }}</strong></div>
                                </div>
                            @endif
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span><i class="fa-solid fa-route me-1" aria-hidden="true"></i> {{ $draft->hasRoute() ? 'Both locations set' : 'Locations to be set' }}</span>
                                <a href="{{ route('booking.edit', ['booking' => $draft, 'step' => 'route']) }}">Edit locations</a>
                            </div>
                        </aside>
                    @endif
                </div>
            @endif
        </div>
        @unless ($step)
            <aside class="col-lg-4">
                <section class="card card-body move-editor-panel product-booking-section" aria-labelledby="vehicle-type-heading">
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
        <small class="text-body-secondary">{{ ['route' => 'Set both pins to continue to your appointment time.', 'time' => 'Next, check your move details and see the quote.'][$step] ?? 'You can update these details while the booking is a draft.' }}</small>
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
