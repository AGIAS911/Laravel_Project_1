<form method="POST"
      action="{{ $service ? route('services.update', $service) : route('services.store') }}">
    @csrf
    @if ($service)
        @method('PUT')
    @endif

    <label>Service Name</label>
    <input name="name" value="{{ old('name', $service ? $service->name : '') }}">
    @error('name') <p>{{ $message }}</p> @enderror

    <label>Caption</label>
    <textarea name="description">{{ old('description', $service ? $service->description : '') }}</textarea>
    @error('description') <p>{{ $message }}</p> @enderror

    <label>Category</label>
    <input name="type" value="{{ old('type', $service ? $service->type : '') }}">
    @error('type') <p>{{ $message }}</p> @enderror

    <label>Unit</label>
    <input name="unit" value="{{ old('unit', $service ? $service->unit : 'fixed') }}">
    @error('unit') <p>{{ $message }}</p> @enderror

    <label>Price</label>
    <input type="number" step="0.001" min="0" name="price"
           value="{{ old('price', $service ? $service->price : '') }}">
    @error('price') <p>{{ $message }}</p> @enderror

    <label>
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $service ? $service->is_active : true))>
        Active
    </label>

    <button type="submit">Save</button>
</form>