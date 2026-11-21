@php
    $scanOnly = $scanOnly ?? false;
    $items = $scanOnly ? $booking->inventoryItems->where('source', 'ai') : $booking->inventoryItems;
    $catalog = config('inventory.items');
    $items = $items->sortBy(fn ($item) => match ($item->status) {
        'suggested' => 0,
        'confirmed' => 1,
        default => 2,
    });
    $confirmed = $items->where('status', 'confirmed')->sum('quantity');
    $suggested = $items->where('status', 'suggested')->sum('quantity');
@endphp

<div data-live-inventory="{{ route('scan-sessions.inventory', ['booking' => $booking, 'scan_only' => (int) $scanOnly], false) }}">
    @if ($items->isEmpty())
        <div class="inventory-empty">
            <i class="fa-solid fa-box-open" aria-hidden="true"></i>
            <div>
                <h3>{{ $scanOnly ? 'No detected items yet' : 'No items added yet' }}</h3>
                <p>{{ $scanOnly ? 'Pair your phone to start scanning.' : 'Scan a room with your phone or add your first item manually.' }}</p>
            </div>
        </div>
    @else
        <div class="inventory-totals" aria-live="polite">
            <span class="inventory-pill is-suggested"><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $suggested }} suggested</span>
            <span class="inventory-pill is-confirmed"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $confirmed }} confirmed</span>
        </div>

        <ul class="inventory-items list-unstyled">
            @foreach ($items as $item)
                @php
                    $entry = $catalog[$item->item_type] ?? [];
                    $name = $entry['label'] ?? str($item->item_type)->headline();
                    $action = match ($item->status) {
                        'suggested' => 'Confirm item',
                        'excluded' => 'Restore item',
                        default => 'Save changes',
                    };
                @endphp
                <li @class(['inventory-item', 'is-suggested' => $item->status === 'suggested'])>
                    <div class="inventory-item-heading">
                        <span class="inventory-item-icon" aria-hidden="true"><i class="fa-solid {{ $entry['icon'] ?? 'fa-box' }}"></i></span>
                        <div>
                            <h3>{{ $name }}</h3>
                            <span>{{ $item->source === 'ai' ? 'Camera scan' : 'Added manually' }}</span>
                        </div>
                        <span @class(['inventory-pill', 'is-'.$item->status])>{{ ucfirst($item->status) }}</span>
                    </div>
                    @if ($item->notes)
                        <p class="inventory-item-note">{{ $item->notes }}</p>
                    @endif

                    <form class="inventory-item-form" data-workflow-form method="POST"
                          action="{{ route('bookings.inventory.update', [$booking, $item], false) }}">
                        @csrf @method('PUT')
                        <div>
                            <label class="form-label" for="item-type-{{ $item->id }}">Item type</label>
                            <select class="form-select" name="item_type" id="item-type-{{ $item->id }}">
                                @foreach ($catalog as $type => $option)
                                    <option value="{{ $type }}" @selected($item->item_type === $type)>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="item-quantity-{{ $item->id }}">Quantity</label>
                            <input class="form-control" id="item-quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="1000" value="{{ $item->quantity }}" required>
                        </div>
                        <div class="inventory-item-actions">
                            <button @class(['btn', 'btn-primary' => $item->status === 'suggested', 'btn-outline-primary' => $item->status !== 'suggested']) name="status" value="confirmed">
                                <i class="fa-solid {{ $item->status === 'excluded' ? 'fa-rotate-left' : 'fa-check' }}" aria-hidden="true"></i> {{ $action }}
                            </button>
                            @if ($item->status !== 'excluded')
                                <button class="btn btn-outline-secondary" name="status" value="excluded">Exclude</button>
                            @endif
                        </div>
                        @if ($booking->booking_method)
                            @php
                                [$defaultWeight, $defaultVolume] = config('inventory.estimates')[$item->item_type] ?? [15, 0.25];
                            @endphp
                            <details class="inventory-estimates">
                                <summary>Adjust weight and volume estimates</summary>
                                <div class="row g-2 mt-1">
                                    <div class="col-6">
                                        <label class="form-label" for="item-weight-{{ $item->id }}">Weight per item (kg)</label>
                                        <input class="form-control" id="item-weight-{{ $item->id }}" type="number" name="estimated_weight_kg"
                                            value="{{ $item->estimated_weight_kg }}" placeholder="{{ $defaultWeight }}" min="0.01" max="10000" step="0.01">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="item-volume-{{ $item->id }}">Volume per item (m³)</label>
                                        <input class="form-control" id="item-volume-{{ $item->id }}" type="number" name="estimated_volume_m3"
                                            value="{{ $item->estimated_volume_m3 }}" placeholder="{{ $defaultVolume }}" min="0.001" max="1000" step="0.001">
                                    </div>
                                </div>
                                <p class="small text-body-secondary mt-2 mb-0">Leave blank to use the estimate for the selected item type.</p>
                            </details>
                        @endif
                    </form>

                    @if ($item->image || $item->source === 'manual')
                        <div class="inventory-item-options">
                            @if ($item->image)
                                <a class="btn btn-link" href="{{ route('bookings.inventory.image', [$booking, $item]) }}" target="_blank" rel="noopener">View photo <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                            @endif
                            @if ($item->source === 'manual')
                                <form data-workflow-form data-workflow-confirm="Remove this item from the booking?" method="POST" action="{{ route('bookings.inventory.destroy', [$booking, $item], false) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link text-danger" type="submit">Remove item</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
        @if (!$scanOnly && $confirmed > 0)
            @php
                $hasMoveDetails = $booking->vehicle_id && $booking->scheduled_start_at?->isFuture()
                    && $booking->pickup_latitude !== null && $booking->pickup_longitude !== null
                    && $booking->destination_latitude !== null && $booking->destination_longitude !== null;
            @endphp
            <div class="inventory-continue">
                <a class="btn btn-outline-primary" href="{{ $booking->booking_method
                    ? route('booking.edit', ['booking' => $booking, 'step' => 'type'])
                    : ($hasMoveDetails ? '#services' : route('booking.edit', $booking)) }}">
                    {{ $booking->booking_method ? 'See recommended vehicle sizes' : ($hasMoveDetails ? 'Continue to services' : 'Set route and time') }}
                    <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
                </a>
            </div>
        @endif
    @endif
</div>
