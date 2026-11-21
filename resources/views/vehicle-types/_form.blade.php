
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <label for="name">Type Name</label>
    <input id="name" name="name" type="text" maxlength="100" required value="{{ old('name', $vehicleType?->name) }}">
    @error('name') <p class="field-error">{{ $message }}</p> @enderror

    <label for="description">Description</label>
    <textarea id="description" name="description">{{ old('description', $vehicleType?->description) }}</textarea>
    @error('description') <p class="field-error">{{ $message }}</p> @enderror

    @if ($vehicleType?->image)
        <p>Current image:</p>
        <img class="thumbnail" src="{{ asset($vehicleType->image) }}" alt="Image of {{ $vehicleType->name }}">
    @endif
    <label for="image">Image</label>
    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
    @error('image') <p class="field-error">{{ $message }}</p> @enderror

    <label>
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vehicleType?->is_active ?? true))>
        Active
    </label>
    @error('is_active') <p class="field-error">{{ $message }}</p> @enderror

    <div class="actions" style="margin-top: 20px">
        <button class="button" type="submit">{{ $method === 'POST' ? 'Create' : 'Save Changes' }}</button>
        <a class="button secondary" href="{{ route('vehicle-types.index') }}">Cancel</a>
    </div>
</form>
