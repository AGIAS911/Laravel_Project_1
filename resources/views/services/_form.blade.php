<div class="card shadow-sm">
    
    <form method="POST"
          action="{{ $service ? route('services.update', $service) : route('services.store') }}">
        @csrf
        @if ($service)
            @method('PUT')
        @endif

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Service name</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $service ? $service->name : '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="type">Category</label>
                    <input class="form-control @error('type') is-invalid @enderror" id="type" name="type"
                           value="{{ old('type', $service ? $service->type : '') }}" required>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                              name="description" rows="3">{{ old('description', $service ? $service->description : '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="unit">Unit</label>
                    <input class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit"
                           value="{{ old('unit', $service ? $service->unit : 'fixed') }}" required>
                    @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="price">Price</label>
                    <input class="form-control @error('price') is-invalid @enderror" id="price" type="number"
                           step="0.001" min="0" name="price"
                           value="{{ old('price', $service ? $service->price : '') }}" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1"
                               @checked(old('is_active', $service ? $service->is_active : true))>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('services.index') }}">Cancel</a>
            <button class="btn btn-primary" type="submit">{{ $service ? 'Update service' : 'Create service' }}</button>
        </div>
    </form>
</div>
