<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ScanSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class ScanSessionController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        abort_unless(
            $booking->customer_id == $request->user()->id,
            403
        );
        abort_unless($booking->status === 'draft', 409);

        $validated = $request->validate([
            'room_name' => 'required|string|max:100',
        ]);

        $baseUrl = rtrim((string) config('scanner.base_url'), '/');
        abort_unless(
            filter_var($baseUrl, FILTER_VALIDATE_URL)
                && parse_url($baseUrl, PHP_URL_SCHEME) === 'https',
            503,
            'Scanner URL is not configured.'
        );

        $token = Str::random(64);
        $scanUrl = $baseUrl.'/scan-test.html#token='.$token;
        $qrCode = new QrCode(data: $scanUrl, size: 300, margin: 10);
        $result = (new SvgWriter())->write($qrCode);

        $scanSession = $booking->scanSessions()->create([
            'room_name' => $validated['room_name'],
            'status' => 'pending',
            'pairing_token_hash' => hash('sha256', $token),
            'pairing_expires_at' => now()->addMinutes(5),
            'expires_at' => now()->addHour(),
        ]);

        return response()->json([
            'scan_session_id' => $scanSession->id,
            'room_name' => $scanSession->room_name,
            'qr_code' => $result->getDataUri(),
            'scan_url' => $scanUrl,
            'expires_at' => $scanSession->pairing_expires_at,
            'status_url' => route('scan-sessions.show', [$booking, $scanSession], false),
            'close_url' => route('scan-sessions.close', [$booking, $scanSession], false),
        ], 201)->header('Cache-Control', 'no-store');
    }

    public function show(Request $request, Booking $booking, ScanSession $scanSession)
    {
        abort_unless($booking->customer_id == $request->user()->id, 403);

        $status = $scanSession->status;
        if ($scanSession->expires_at->isPast()
            || ($status === 'pending' && !$scanSession->pairing_expires_at?->isFuture())) {
            $status = 'expired';
        }

        return response()->json(['status' => $status])->header('Cache-Control', 'no-store');
    }

    public function pair(Request $request)
    {
        $validated = $request->validate([
            'pairing_token' => 'required|string|size:64',
        ]);

        return DB::transaction(function () use ($validated) {
            $scanSession = ScanSession::where(
                'pairing_token_hash',
                hash('sha256', $validated['pairing_token'])
            )->lockForUpdate()->firstOrFail();

            abort_unless($scanSession->status === 'pending', 409);

            abort_if(
                !$scanSession->pairing_expires_at ||
                $scanSession->pairing_expires_at->isPast() ||
                $scanSession->expires_at->isPast(),
                410
            );
            abort_unless($scanSession->booking()->where('status', 'draft')->exists(), 409);

            $deviceToken = Str::random(64);

            $scanSession->update([
                'status' => 'paired',
                'device_token_hash' => hash('sha256', $deviceToken),
                'paired_at' => now(),
                'pairing_expires_at' => null,
                'pairing_token_hash' => null,
            ]);

            return response()->json([
                'scan_session_id' => $scanSession->id,
                'room_name' => $scanSession->room_name,
                'device_token' => $deviceToken,
            ])->header('Cache-Control', 'no-store');
        });
    }

    public function device(Request $request)
    {
        $session = $this->deviceSession($request);
        abort_unless($session->booking()->where('status', 'draft')->exists(), 409);

        return response()->noContent()->header('Cache-Control', 'no-store');
    }

    public function sync(Request $request)
    {
        $data = $request->validate([
            'items' => ['present', 'array', 'max:12'],
            'items.*.item_type' => ['required', 'distinct', Rule::in(InventoryController::ITEM_TYPES)],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);
        $session = $this->deviceSession($request);
        DB::transaction(function () use ($request, $session, $data) {
            $booking = Booking::lockForUpdate()->findOrFail($session->booking_id);
            abort_unless($booking->status === 'draft', 409);
            $session = $this->deviceSession($request, lock: true);
            foreach ($data['items'] as $item) {
                $key = 'summary:'.$item['item_type'];
                $saved = $session->inventoryItems()->firstOrNew(['tracking_key' => $key]);
                if (!$saved->exists || $saved->status === 'suggested') {
                    $item['quantity'] = max($item['quantity'], $saved->quantity ?? 0);
                    $saved->fill($item + [
                        'booking_id' => $booking->id, 'source' => 'ai', 'status' => 'suggested',
                    ])->save();
                }
            }
        });

        return response()->noContent();
    }

    public function close(Request $request, Booking $booking, ScanSession $scanSession)
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);
        $scanSession->update([
            'status' => 'closed',
            'pairing_token_hash' => null,
            'device_token_hash' => null,
        ]);

        return response()->noContent();
    }

    private function deviceSession(Request $request, bool $lock = false): ScanSession
    {
        $token = $request->bearerToken();
        abort_unless(is_string($token) && preg_match('/^[a-zA-Z0-9]{64}$/D', $token), 401);
        $session = ScanSession::where('device_token_hash', hash('sha256', $token))
            ->when($lock, fn ($query) => $query->lockForUpdate())->first();
        abort_unless($session?->status === 'paired' && $session->expires_at->isFuture(), 401);

        return $session;
    }
}
