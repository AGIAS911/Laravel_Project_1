<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <label for="name">Vehicle Name</label>
    <input id="name" name="name" type="text" maxlength="150" required value="{{ old('name', $vehicle?->name) }}">
    @error('name') <p class="field-error">{{ $message }}</p> @enderror

    <label for="plate_number">License Plate</label>
    <input id="plate_number" name="plate_number" type="text" maxlength="30" required value="{{ old('plate_number', $vehicle?->plate_number) }}">
    @error('plate_number') <p class="field-error">{{ $message }}</p> @enderror

    <label for="vehicle_type_id">Vehicle Type</label>
    <select id="vehicle_type_id" name="vehicle_type_id" required>
        <option value="">Select a vehicle type</option>
        @foreach ($vehicleTypes as $vehicleType)
            <option value="{{ $vehicleType->id }}" @selected((string) old('vehicle_type_id', $vehicle?->vehicle_type_id) === (string) $vehicleType->id)>
                {{ $vehicleType->name }}
            </option>
        @endforeach
    </select>
    @error('vehicle_type_id') <p class="field-error">{{ $message }}</p> @enderror

    <label for="driver_id">Driver</label>
    <select id="driver_id" name="driver_id" required>
        <option value="">Select a driver</option>
        @foreach ($drivers as $driver)
            <option value="{{ $driver->id }}" @selected((string) old('driver_id', $vehicle?->driver_id) === (string) $driver->id)>
                {{ $driver->license_number }} ({{ $driver->user_id }})
            </option>
        @endforeach
    </select>
    @error('driver_id') <p class="field-error">{{ $message }}</p> @enderror

    @if ($vehicle?->image)
        <p>Current image:</p>
        <img class="thumbnail" src="{{ asset($vehicle->image) }}" alt="Image of {{ $vehicle->name }}">
    @endif
    <label for="image">Vehicle Image</label>
    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
    @error('image') <p class="field-error">{{ $message }}</p> @enderror

    @php($capacities = ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'])
    @foreach (['capacity_kg' => 'Weight Capacity', 'capacity_m3' => 'Volume Capacity'] as $field => $label)
        <label for="{{ $field }}">{{ $label }}</label>
        <select id="{{ $field }}" name="{{ $field }}" required>
            @foreach ($capacities as $value => $capacityLabel)
                <option value="{{ $value }}" @selected(old($field, data_get($vehicle, $field, '')) === $value)>{{ $capacityLabel }}</option>
            @endforeach
        </select>
        @error($field) <p class="field-error">{{ $message }}</p> @enderror
    @endforeach

    <label for="base_price">Base Price</label>
    <input id="base_price" name="base_price" type="number" min="0" step="0.001" required value="{{ old('base_price', $vehicle?->base_price) }}">
    @error('base_price') <p class="field-error">{{ $message }}</p> @enderror

    <label for="price_per_km">Price per Kilometer</label>
    <input id="price_per_km" name="price_per_km" type="number" min="0" step="0.001" required value="{{ old('price_per_km', $vehicle?->price_per_km) }}">
    @error('price_per_km') <p class="field-error">{{ $message }}</p> @enderror

    <label for="status">Status</label>
    <select id="status" name="status" required>
        <option value="active" @selected(old('status', $vehicle?->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $vehicle?->status) === 'inactive')>Inactive</option>
        <option value="maintenance" @selected(old('status', $vehicle?->status) === 'maintenance')>Maintenance</option>
    </select>
    @error('status') <p class="field-error">{{ $message }}</p> @enderror

    <div class="actions" style="margin-top: 20px">
        <button class="button" type="submit">{{ $method === 'POST' ? 'Add Vehicle' : 'Save Changes' }}</button>
        <a class="button secondary" href="{{ route('vehicles.index') }}">Cancel</a>
    </div>
</form>
