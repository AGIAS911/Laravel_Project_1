    <div class="modal fade" id="furniture-scan" tabindex="-1" aria-labelledby="furniture-scan-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="furniture-scan-title"><i class="fa-solid fa-qrcode me-2" aria-hidden="true"></i> Scan furniture with your phone</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-body-secondary">Generate a QR code, then scan it with your phone camera.</p>
                    <form id="scan-form">
                        @csrf
                        <button type="submit" class="btn btn-primary">Generate QR code</button>
                    </form>
                    <div id="scan-qr-panel" class="text-center mt-3" hidden>
                        <img id="scan-qr" class="img-fluid" width="300" height="300" alt="QR code to scan with your phone">
                        <p class="mb-0">Expires in <span id="scan-countdown">5:00</span></p>
                        <button type="button" id="scan-copy" class="btn btn-outline-secondary btn-sm mt-2">Copy phone link</button>
                    </div>
                    <button type="button" id="scan-close" class="btn btn-outline-danger btn-sm mt-2" hidden>End scan session</button>
                    <p id="scan-status" class="mb-0" role="status"></p>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/booking-scanner.js') }}" defer></script>
