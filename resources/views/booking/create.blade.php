@extends('layouts.dashboard')

@section('title', 'New booking | MoveIt')
@section('page-title', 'New booking')
@section('page-description', 'Step 1: Furniture inventory (item type and quantity only).')

@section('content')
    @include('booking._steps', ['current' => 'inventory', 'line' => true])

    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <section class="card card-body inventory-panel">
                <h2 class="h5 workspace-heading mb-4">Add item manually</h2>
                <form method="POST" action="{{ route('bookings.inventory.start') }}" enctype="multipart/form-data">
                    @csrf
                    @include('booking._inventory-fields')
                    <button class="btn btn-primary mt-4" type="submit"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i> Add item</button>
                </form>
            </section>
        </div>
        <aside class="col-lg-4 d-grid gap-3">
            <section class="card card-body inventory-panel">
                <h2 class="h5 workspace-heading"><i class="fa-solid fa-camera inventory-accent me-1" aria-hidden="true"></i> Camera scan</h2>
                <p class="text-body-secondary mt-2">Scan a room with your phone, then review the suggested items and quantities.</p>
                <form method="POST" action="{{ route('booking.store') }}" class="d-grid gap-2">
                    @csrf
                    <input type="hidden" name="open_phone_scan" value="1">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-video me-1" aria-hidden="true"></i> Start live scan</button>
                    <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-mobile-screen me-1" aria-hidden="true"></i> Use your phone camera</button>
                </form>
            </section>
            <div class="inventory-tip"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><span>No weight or dimensions are needed. If scanning fails, you can continue manually.</span></div>
        </aside>
    </div>
@endsection
