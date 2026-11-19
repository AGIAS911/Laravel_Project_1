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
    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <div class="scan-preview">
                <video id="scan-demo-video" muted loop playsinline preload="metadata" poster="{{ asset('landing/img/scan.jpg') }}" aria-label="Example furniture scan">
                    <source src="{{ asset('landing/video/hero.mp4') }}" type="video/mp4">
                </video>
                <span class="scan-preview-label"><i class="fa-solid fa-flask me-1" aria-hidden="true"></i> Simulation</span>
                <span class="scan-preview-state" id="scan-preview-state" aria-live="polite">Preview paused</span>
            </div>
            <div class="d-flex flex-wrap gap-2 my-3" aria-label="Simulation controls">
                <button class="btn btn-success" type="button" data-scan-preview="play"><i class="fa-solid fa-play me-1" aria-hidden="true"></i> Start preview</button>
                <button class="btn btn-outline-secondary" type="button" data-scan-preview="pause"><i class="fa-solid fa-pause me-1" aria-hidden="true"></i> Pause</button>
                <button class="btn btn-outline-secondary" type="button" data-scan-preview="stop"><i class="fa-solid fa-stop me-1" aria-hidden="true"></i> Reset</button>
            </div>

            <section class="card card-body inventory-panel" aria-labelledby="scan-items-title">
                <h2 class="h5 workspace-heading mb-3" id="scan-items-title">Detected items</h2>
                <p class="small text-body-secondary" data-workflow-status role="status"></p>
                @include('booking._inventory-list', ['scanOnly' => true])
            </section>
        </div>

        <aside class="col-lg-4 d-grid gap-3">
            <section class="card card-body inventory-panel scan-pair-panel" id="furniture-scan" data-scan-url="{{ route('scan-sessions.store', $booking, false) }}">
                <h2 class="h5 workspace-heading text-center">Use your phone camera</h2>
                <p class="text-body-secondary text-center">Scan the QR code with your phone. Detected items appear here for review.</p>
                <form id="scan-form">
                    @csrf
                    <label class="form-label" for="scan-room">Room name</label>
                    <input class="form-control mb-3" id="scan-room" name="room_name" placeholder="Living room" maxlength="100" required>
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-qrcode me-1" aria-hidden="true"></i> Generate QR code</button>
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
