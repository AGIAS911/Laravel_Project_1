@extends('layouts.app')

@php
    $editing = $vehicle !== null;
    $heading = $editing ? 'Edit vehicle' : 'Add vehicle';
    $fields = [
        'name' => 'Vehicle name',
        'plate_number' => 'License plate',
        'vehicle_type_id' => 'Vehicle type',
        'status' => 'Status',
        'capacity_kg' => 'Weight capacity',
        'capacity_m3' => 'Volume capacity',
        'base_price' => 'Base price',
        'price_per_km' => 'Price per km',
        'image' => 'Vehicle photo',
    ];
    $capacityOptions = ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'];
    $selectOptions = [
        'vehicle_type_id' => $vehicleTypes->pluck('name', 'id'),
        'status' => ['active' => 'Active', 'maintenance' => 'Maintenance', 'inactive' => 'Inactive'],
        'capacity_kg' => $capacityOptions,
        'capacity_m3' => $capacityOptions,
    ];
    $fieldGroups = [
        ['Vehicle details', 'fa-truck', ['name', 'plate_number', 'vehicle_type_id', 'status']],
        ['Capacity', 'fa-boxes-stacked', ['capacity_kg', 'capacity_m3']],
        ['Rates', 'fa-coins', ['base_price', 'price_per_km']],
    ];
@endphp

@section('title', $heading . ' | MoveIt')

@section('content')
    <div class="driver-vehicle-editor">
        <header class="driver-vehicle-heading product-operator-heading">
            <a class="driver-vehicle-back" href="{{ route('vehicles.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> My vehicles</a>
            <h1>{{ $heading }}</h1>
            <p>{{ $editing ? 'Update the details, rates, and availability for this vehicle.' : 'Enter your vehicle details and set the rates customers will see.' }}</p>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Check the highlighted fields.</strong>
                <ul class="mb-0">
                    @foreach ($errors->keys() as $field)
                        @if (isset($fields[$field]))
                            <li><a class="alert-link" href="#{{ $field }}">{{ $fields[$field] }}</a></li>
                        @else
                            <li>{{ $errors->first($field) }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($vehicleTypes->isEmpty())
            <p class="alert alert-warning" role="status">No vehicle types are available. <a class="alert-link" href="{{ route('info.contact') }}">Contact support</a> for help.</p>
        @endif

        <form method="POST" action="{{ $editing ? route('vehicles.update', $vehicle) : route('vehicles.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="driver-vehicle-grid">
                <div class="driver-vehicle-panel">
                    @foreach ($fieldGroups as [$group, $icon, $groupFields])
                        <fieldset class="driver-vehicle-group">
                            <legend><i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $group }}</legend>
                            <div class="driver-vehicle-fields">
                                @foreach ($groupFields as $field)
                                    @php
                                        $isRate = in_array($field, ['base_price', 'price_per_km'], true);
                                        $value = old($field, data_get($vehicle, $field, $field === 'status' ? 'active' : null));
                                    @endphp
                                    <div class="driver-vehicle-field">
                                        <label class="form-label" for="{{ $field }}">{{ $fields[$field] }}@if ($isRate) <span>(JOD)</span>@endif</label>
                                        @if (isset($selectOptions[$field]))
                                            <select @class(['form-select', 'is-invalid' => $errors->has($field)]) id="{{ $field }}" name="{{ $field }}" required
                                                @if ($errors->has($field)) aria-invalid="true" aria-describedby="{{ $field }}-error"
                                                @elseif ($field === 'status') aria-describedby="vehicle-status-help" @endif>
                                                @if ($field !== 'status')
                                                    <option value="">{{ $field === 'vehicle_type_id' ? 'Choose a vehicle type' : 'Choose a size' }}</option>
                                                @endif
                                                @foreach ($selectOptions[$field] as $option => $label)
                                                    <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @if ($field === 'status')
                                                <div class="form-text" id="vehicle-status-help">Only active vehicles can receive new bookings.</div>
                                            @endif
                                        @else
                                            <input @class(['form-control', 'is-invalid' => $errors->has($field)]) id="{{ $field }}" name="{{ $field }}"
                                                type="{{ $isRate ? 'number' : 'text' }}" value="{{ $value }}" required
                                                @if ($isRate) min="0" step="0.001" inputmode="decimal" @else maxlength="{{ $field === 'name' ? 150 : 30 }}" @endif
                                                @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                                        @endif
                                        @error($field)<div class="invalid-feedback" id="{{ $field }}-error">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>

                <section class="driver-vehicle-panel driver-vehicle-photo" aria-labelledby="vehicle-photo-heading">
                    <h2 id="vehicle-photo-heading"><i class="fa-regular fa-image" aria-hidden="true"></i> Vehicle photo <span>Optional</span></h2>
                    @if ($vehicle?->image)
                        <img class="driver-vehicle-photo-current" src="{{ asset($vehicle->image) }}" alt="Current photo of {{ $vehicle->name }}">
                    @else
                        <div class="driver-vehicle-photo-art">
                            <img src="{{ asset('landing/img/driver-vehicle-truck.svg') }}" alt="" width="132" height="68">
                        </div>
                    @endif
                    <label class="form-label" for="image">{{ $vehicle?->image ? 'Replace photo' : 'Upload photo' }}</label>
                    <input @class(['form-control', 'is-invalid' => $errors->has('image')]) id="image" name="image" type="file"
                        accept="image/jpeg,image/png,image/webp" aria-describedby="vehicle-photo-help @error('image') image-error @enderror"
                        @error('image') aria-invalid="true" @enderror>
                    <div class="form-text" id="vehicle-photo-help">JPG, PNG, or WebP. Up to 2 MB.</div>
                    @error('image')<div class="invalid-feedback" id="image-error">{{ $message }}</div>@enderror
                </section>
            </div>

            <div class="driver-vehicle-actions">
                <button class="driver-button driver-fleet-add" type="submit" @disabled($vehicleTypes->isEmpty())>
                    <i class="fa-solid {{ $editing ? 'fa-check' : 'fa-plus' }}" aria-hidden="true"></i>
                    {{ $editing ? 'Save changes' : 'Add vehicle' }}
                </button>
                <a class="driver-button driver-fleet-edit" href="{{ route('vehicles.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
