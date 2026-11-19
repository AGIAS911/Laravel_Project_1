<fieldset class="inventory-type-fieldset">
    <legend class="visually-hidden">Furniture type</legend>
    <div class="inventory-type-grid">
        @foreach ([
            ['sofa', 'Sofa', 'fa-couch'],
            ['bed', 'Bed', 'fa-bed'],
            ['wardrobe', 'Cabinet', 'fa-door-closed'],
            ['refrigerator', 'Refrigerator', 'fa-snowflake'],
            ['washing machine', 'Washing machine', 'fa-soap'],
            ['dining table', 'Table', 'fa-table-cells-large'],
            ['chair', 'Chair', 'fa-chair'],
            ['tv', 'TV', 'fa-tv'],
            ['desk', 'Desk', 'fa-computer'],
            ['mirror', 'Mirror', 'fa-image'],
            ['other', 'Other', 'fa-box'],
        ] as [$type, $label, $icon])
            <label class="inventory-type">
                <input class="visually-hidden" type="radio" name="item_type" value="{{ $type }}" @checked(old('item_type', 'sofa') === $type) required>
                <span><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    @error('item_type')<p class="text-danger small mt-2">{{ $message }}</p>@enderror
</fieldset>

<div class="row g-3 mt-1">
    <div class="col-sm-6">
        <label class="form-label fw-semibold" for="item_quantity">Count</label>
        <input class="form-control" id="item_quantity" name="quantity" type="number" min="1" max="1000" value="{{ old('quantity', 1) }}" required>
        @error('quantity')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label fw-semibold" for="inventory_image">Image <span class="fw-normal text-body-secondary">(optional)</span></label>
        <input class="form-control" id="inventory_image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
        @error('image')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold" for="inventory_notes">Notes <span class="fw-normal text-body-secondary">(optional)</span></label>
        <textarea class="form-control" id="inventory_notes" name="notes" maxlength="2000" rows="2" placeholder="Example: glass, handle with care">{{ old('notes') }}</textarea>
        @error('notes')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
    </div>
</div>
