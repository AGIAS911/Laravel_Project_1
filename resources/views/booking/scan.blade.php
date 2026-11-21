@extends('layouts.dashboard')

@section('title', 'Furniture scan | MoveIt')
@section('page-title', 'Live scan and phone pairing')
@section('page-description', 'Pair your phone to scan furniture for this booking.')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('booking.show', $booking) }}#inventory">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> Back to inventory
    </a>
@endsection

@section('content')
    @if ($booking->booking_method)
        @include('booking._wizard-steps', ['current' => 'inventory'])
    @else
        @include('booking._steps', ['current' => 'inventory', 'line' => true])
    @endif
    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <section class="card card-body inventory-panel" aria-labelledby="scan-items-title" data-workflow-scope>
                <h2 class="h5 workspace-heading mb-3" id="scan-items-title">Detected items</h2>
                <p class="inventory-feedback" data-workflow-status role="status"></p>
                @include('booking._inventory-list', ['scanOnly' => true])
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if ($booking->booking_method)
                        <a class="btn btn-primary" href="{{ route('booking.show', $booking) }}#inventory">Review inventory</a>
                    @else
                        <a class="btn btn-primary" href="{{ route('booking.edit', $booking) }}">Continue to locations and schedule</a>
                        <a class="btn btn-outline-secondary" href="{{ route('booking.show', $booking) }}#inventory">Add items manually</a>
                    @endif
                </div>
            </section>
        </div>

        <aside class="col-lg-4 d-grid gap-3">
            <section class="card card-body inventory-panel scan-pair-panel" id="furniture-scan" data-scan-url="{{ route('scan-sessions.store', $booking, false) }}">
                <h2 class="h5 workspace-heading text-center">Use your phone camera</h2>
                <p id="scan-instructions" class="text-body-secondary text-center">Scan the QR code with your phone. Detected items appear here for review.</p>
                <form id="scan-form">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">Generate QR code</button>
                </form>
                <div id="scan-qr-panel" class="text-center mt-3" hidden>
                    <img id="scan-qr" width="220" height="220" alt="Phone pairing QR code">
                    <p class="small text-body-secondary mt-2 mb-1">Code expires in <span id="scan-countdown"></span></p>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="scan-copy"><i class="fa-regular fa-copy me-1" aria-hidden="true"></i> Copy link</button>
                </div>
                <p id="scan-status" class="scan-pair-status text-center small mt-3 mb-0" role="status">Not connected</p>
                <button type="button" id="scan-renew" class="btn btn-link mt-2" hidden><i class="fa-solid fa-rotate me-1" aria-hidden="true"></i> New code</button>
                <button type="button" id="scan-close" class="btn btn-outline-danger w-100 mt-3" hidden>End session</button>
            </section>
            <div class="inventory-tip">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                <span>The pairing code is temporary and single-use. Phone scanning requires HTTPS.</span>
            </div>
        </aside>
    </div>

    <script src="{{ asset('js/booking-workflow.js') }}" defer></script>
    <script src="{{ asset('js/booking-scanner.js') }}" defer></script>
@endsection
