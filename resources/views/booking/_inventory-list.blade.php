<div data-live-inventory>
    @forelse ((($scanOnly ?? false) ? $booking->inventoryItems->where('source', 'ai') : $booking->inventoryItems) as $item)
        <div class="border-bottom py-2 d-flex flex-wrap gap-2 align-items-center">
            <form class="d-flex flex-wrap gap-2 align-items-center" data-workflow-form method="POST"
                  action="{{ route('bookings.inventory.update', [$booking, $item], false) }}">
                @csrf @method('PUT')
                <label class="visually-hidden" for="item-type-{{ $item->id }}">Item type</label>
                <select class="form-select w-auto" name="item_type" id="item-type-{{ $item->id }}">
                    @foreach (\App\Http\Controllers\InventoryController::ITEM_TYPES as $type)
                        <option @selected($item->item_type === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <label class="visually-hidden" for="item-quantity-{{ $item->id }}">Quantity</label>
                <input class="form-control w-auto" style="max-width:6rem" id="item-quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="1000" value="{{ $item->quantity }}" required>
                <span class="badge text-bg-light">{{ ucfirst($item->status) }}</span>
                <button class="btn btn-success btn-sm" name="status" value="confirmed"><i class="fa-solid fa-check me-1" aria-hidden="true"></i> Confirm</button>
                @if ($item->status !== 'excluded')
                    <button class="btn btn-outline-secondary btn-sm" name="status" value="excluded">Exclude</button>
                @endif
            </form>
            @if ($item->source === 'manual')
                <form data-workflow-form method="POST" action="{{ route('bookings.inventory.destroy', [$booking, $item], false) }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">Remove</button>
                </form>
            @endif
        </div>
    @empty
        <p class="text-body-secondary">{{ ($scanOnly ?? false) ? 'No detected items yet. Pair your phone to start scanning.' : 'Add items manually or scan them with your phone.' }}</p>
    @endforelse
</div>
