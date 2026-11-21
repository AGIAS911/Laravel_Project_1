@php
    $selectedType = old('item_type', 'sofa');
    [$moreTypes, $commonTypes] = collect(config('inventory.items'))->partition(fn ($item) => $item['extended'] ?? false);
@endphp
<fieldset class="inventory-type-fieldset">
    <legend class="visually-hidden">Household item type</legend>
    @foreach ([$commonTypes, $moreTypes] as $types)
        @unless ($loop->first)
            <details class="inventory-more-types mt-3" @if (is_string($selectedType) && $moreTypes->has($selectedType)) open @endif>
                <summary>More household items</summary>
        @endunless
        <div class="inventory-type-grid">
            @foreach ($types as $type => $item)
                <label class="inventory-type">
                    <input class="visually-hidden" type="radio" name="item_type" value="{{ $type }}" @checked($selectedType === $type) required>
                    <span><i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}</span>
                </label>
            @endforeach
        </div>
        @unless ($loop->first)</details>@endunless
    @endforeach
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
