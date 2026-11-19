<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Customer side of CliQ deposit payments (upload receipt, see payment history).
 */
class PaymentController extends Controller
{
    /**
     * Payment history for the customer's bookings.
     */
    public function index(Request $request)
    {
        $payments = Payment::with('booking')
            ->whereHas('booking', fn ($q) => $q->where('customer_id', $request->user()->id))
            ->latest('id')
            ->paginate(10);

        $awaiting = $request->user()->bookings()
            ->where('status', 'awaiting_deposit')
            ->orderByDesc('id')
            ->get();

        return view('payments.index', compact('payments', 'awaiting'));
    }

    /**
     * Show the CliQ details and the receipt upload form.
     */
    public function create(Request $request)
    {
        $booking = $this->payableBooking($request, $request->query('booking_id'));

        if ($this->hasPending($booking)) {
            return redirect()->route('payments.index')
                ->with('success', __('payments.already_pending'));
        }

        return view('payments.create', compact('booking'));
    }

    /**
     * Save a new payment proof as a pending record.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.001', 'max:9999999'],
            'transfer_reference' => ['nullable', 'string', 'max:100'],
            'receipt_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $data) {
            // Lock the booking so two quick submits cannot both create a pending proof.
            $booking = $this->payableBooking($request, $data['booking_id'], true);

            if ($this->hasPending($booking)) {
                $this->fail('receipt_image', __('payments.already_pending'));
            }

            if (Payment::referenceInUse($data['transfer_reference'] ?? null)) {
                $this->fail('transfer_reference', __('payments.reference_used'));
            }

            // Private disk: the file is never reachable by a public URL.
            $path = $request->file('receipt_image')->store('receipts', 'local');

            Payment::create([
                'booking_id' => $booking->id,
                'purpose' => Payment::PURPOSE_DEPOSIT,
                'amount' => $data['amount'],
                'transfer_reference' => $data['transfer_reference'] ?? null,
                'receipt_image' => $path,
                'status' => Payment::STATUS_PENDING,
            ]);
        });

        return redirect()->route('payments.index')
            ->with('success', __('payments.submitted'));
    }

    /**
     * Stream the receipt image to its owner or an admin only.
     */
    public function receipt(Request $request, string $id)
    {
        $payment = Payment::with('booking')->findOrFail($id);
        $user = $request->user();

        abort_unless($user->role === 'admin' || $payment->booking->customer_id === $user->id, 404);
        abort_unless(Storage::disk('local')->exists($payment->receipt_image), 404);

        return Storage::disk('local')->response($payment->receipt_image);
    }

    /**
     * A booking of the current customer that is still waiting for the deposit.
     */
    private function payableBooking(Request $request, $bookingId, bool $lock = false): Booking
    {
        $query = $request->user()->bookings()->where('status', 'awaiting_deposit');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($bookingId);
    }

    private function hasPending(Booking $booking): bool
    {
        return $booking->payments()->where('status', Payment::STATUS_PENDING)->exists();
    }

    private function fail(string $field, string $message): void
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
