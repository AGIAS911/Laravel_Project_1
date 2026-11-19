@php($editing = isset($draft))

    <section class="card card-body p-3 p-md-4 mb-4" aria-labelledby="booking-form-title">
        <h2 id="booking-form-title" class="h5">{{ $editing ? __('Edit locations and schedule') : __('Locations and schedule') }}</h2>
        <p class="text-body-secondary">Vehicle and schedule are optional. Enter both coordinates for each location you provide.</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <p class="mb-1">Check the following fields:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route($editing ? 'booking.update' : 'booking.store', $draft ?? []) }}">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12" id="booking-map-panel" hidden>
                    <fieldset class="mb-2">
                        <legend class="fs-6 fw-semibold">Choose locations on the map</legend>
                        <label class="me-3"><input type="radio" name="map_target" value="pickup" checked> Pickup</label>
                        <label><input type="radio" name="map_target" value="destination"> Destination</label>
                    </fieldset>
                    <div id="booking-map" class="border rounded" style="height:300px"></div>
                    <p class="small text-body-secondary mb-0">Select pickup or destination, then tap the map. You can also enter coordinates below.</p>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="vehicle_id">Vehicle</label>
                    @php($selectedVehicle = old('vehicle_id', $draft?->vehicle_id))
                    <select class="form-select" id="vehicle_id" name="vehicle_id">
                        <option value="">Choose later</option>
                        @if ($selectedVehicle)
                            <option value="{{ $selectedVehicle }}" selected>Selected vehicle #{{ $selectedVehicle }}</option>
                        @endif
                    </select>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" data-find-vehicles="{{ route('bookings.vehicles', [], false) }}">Find available vehicles</button>
                    <p data-vehicle-status class="small mt-2" role="status"></p>
                </div>

                @foreach (['pickup' => 'Pickup', 'destination' => 'Destination'] as $key => $label)
                    <fieldset class="col-12">
                        <legend class="fs-6 fw-semibold">{{ $label }}</legend>
                        <div class="row g-2">
                            @foreach (['latitude' => 'Latitude', 'longitude' => 'Longitude', 'floor' => 'Floor'] as $field => $fieldLabel)
                                @php($name = $key . '_' . $field)
                                <div class="col-sm">
                                    <label class="form-label" for="{{ $name }}">{{ $fieldLabel }}</label>
                                    <input class="form-control" inputmode="{{ $field === 'floor' ? 'numeric' : 'decimal' }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $draft?->{$name} ?? ($field === 'floor' ? 0 : '')) }}">
                                </div>
                            @endforeach
                            @php($name = $key . '_has_elevator')
                            <div class="col-sm">
                                <label class="form-label" for="{{ $name }}">Elevator</label>
                                <select class="form-select" id="{{ $name }}" name="{{ $name }}">
                                    <option value="0" @selected(!old($name, $draft?->{$name}))>No</option>
                                    <option value="1" @selected(old($name, $draft?->{$name}))>Yes</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                @endforeach

                @foreach (['scheduled_start_at' => 'Scheduled start', 'scheduled_end_at' => 'Scheduled end'] as $name => $label)
                    <div class="col-sm-6">
                        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
                        <input class="form-control" type="datetime-local" step="1" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $draft?->{$name}?->timezone(config('app.timezone'))->format('Y-m-d\TH:i:s')) }}">
                    </div>
                @endforeach

                <div class="col-12">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $draft?->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary" type="submit">{{ $editing ? __('Save draft') : __('Create draft') }}</button>
            </div>
        </form>
    </section>
    <script src="{{ asset('js/booking-workflow.js') }}" defer></script>
    @once
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
        @endpush
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous" defer></script>
        <script src="{{ asset('js/booking-map.js') }}" defer></script>
    @endonce
