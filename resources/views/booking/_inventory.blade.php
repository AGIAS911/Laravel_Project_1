<section class="card booking-inventory mb-3" id="inventory" tabindex="-1" aria-labelledby="booking-inventory-title" data-workflow-scope data-workflow-return="inventory">
    <div class="card-body">
        <div class="booking-inventory-heading">
            <span class="booking-inventory-icon" aria-hidden="true"><i class="fa-solid fa-boxes-stacked"></i></span>
            <div>
                <h2 class="h5 workspace-heading" id="booking-inventory-title">Furniture inventory</h2>
                <p>Add the items you’re moving and confirm anything suggested by the camera scan.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('scan-sessions.workspace', $booking) }}">
                <i class="fa-solid fa-mobile-screen me-1" aria-hidden="true"></i> Scan with phone
            </a>
        </div>

        <p class="inventory-feedback" data-workflow-status role="status"></p>
        @include('booking._inventory-list')

        <details class="booking-inventory-add" @if ($errors->hasAny(['item_type', 'quantity', 'image', 'notes'])) open @endif>
            <summary>
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Add item manually
                <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
            </summary>
            <form data-workflow-form method="POST" action="{{ route('bookings.inventory.store', $booking, false) }}" enctype="multipart/form-data">
                @csrf
                @include('booking._inventory-fields')
                <button class="btn btn-primary mt-3" type="submit"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i> Add to inventory</button>
            </form>
        </details>
    </div>
</section>
