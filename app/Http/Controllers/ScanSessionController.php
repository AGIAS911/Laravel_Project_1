<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ScanSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ScanSessionController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id == $request->user()->id, 403);

        $validated = $request->validate([
            'room_name' => 'required|string|max:100',
        ]);

        $token = Str::random(64);

        $scanSession = $booking->scanSessions()->create([
            'room_name' => $validated['room_name'],
            'status' => 'pending',
            'pairing_token_hash' => hash('sha256', $token),
            'pairing_expires_at' => now()->addMinutes(5),
            'expires_at' => now()->addHour(),
        ]);

        return response()->json([
            'scan_session' => $scanSession,
            'pairing_token' => $token,
        ], 201);
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

            $deviceToken = Str::random(64);

            $scanSession->update([
                'status' => 'paired',
                'device_token_hash' => hash('sha256', $deviceToken),
                'paired_at' => now(),
                'pairing_expires_at' => null,
                'pairing_token_hash' => null,
            ]);

            return response()->json([
                'scan_session' => $scanSession,
                'device_token' => $deviceToken,
            ]);
        });
    }
}