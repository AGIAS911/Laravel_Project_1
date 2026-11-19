<section class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h5 mb-0">Furniture inventory</h2>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#furniture-scan"
                    data-scan-url="{{ route('scan-sessions.store', $booking, false) }}">Scan furniture by QR</button>
        </div>
        <div data-live-inventory>
            @forelse ($booking->inventoryItems as $item)
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
                        <button class="btn btn-outline-primary btn-sm" name="status" value="confirmed">Confirm</button>
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
                <p class="text-body-secondary">Add items manually or scan them with your phone.</p>
            @endforelse
        </div>
        <form class="row g-2 align-items-end mt-2" data-workflow-form method="POST" action="{{ route('bookings.inventory.store', $booking, false) }}">
            @csrf
            <div class="col-sm">
                <label class="form-label" for="item_type">Furniture type</label>
                <select class="form-select" name="item_type" id="item_type">
                    @foreach (\App\Http\Controllers\InventoryController::ITEM_TYPES as $type)
                        <option>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label" for="item_quantity">Quantity</label>
                <input class="form-control" id="item_quantity" name="quantity" type="number" min="1" max="1000" value="1" required>
            </div>
            <div class="col-auto"><button class="btn btn-outline-primary">Add item</button></div>
        </form>
    </div>
</section>
