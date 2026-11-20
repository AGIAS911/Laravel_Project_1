<section class="card mb-3" id="inventory">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h5 workspace-heading mb-0">Furniture inventory</h2>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('scan-sessions.workspace', $booking) }}">
                <i class="fa-solid fa-qrcode me-1" aria-hidden="true"></i> Scan furniture
            </a>
        </div>
        @include('booking._inventory-list')
        <form class="mt-4" data-workflow-form method="POST" action="{{ route('bookings.inventory.store', $booking, false) }}" enctype="multipart/form-data">
            @csrf
            <h3 class="h6 workspace-heading mb-3">Add item manually</h3>
            @include('booking._inventory-fields')
            <button class="btn btn-primary mt-3" type="submit"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i> Add item</button>
        </form>
    </div>
</section>
