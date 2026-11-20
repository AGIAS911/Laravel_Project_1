<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MoveItNotification;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use App\Models\Service;
use App\Models\Vehicle;
use Carbon\Carbon;
use App\Models\DriverProfile;
use App\Models\BookingService;
use App\Models\ScanSession;
use App\Models\InventoryItem;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
            ->withExists(['payments as receipt_under_review' => fn ($payments) => $payments->where('status', Payment::STATUS_PENDING)->where('purpose', Payment::PURPOSE_DEPOSIT)])
            ->where('status', 'awaiting_deposit')
            ->orderByDesc('id')
            ->get();

        return view('payments.index', compact('payments', 'awaiting') + ['cliqConfigured' => PlatformSetting::cliqConfigured()]);
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

        $beneficiary = PlatformSetting::values();
        $previousProof = $booking->payments()->latest('id')->first();

        return view('payments.create', compact('booking', 'beneficiary', 'previousProof') + ['cliqConfigured' => PlatformSetting::cliqConfigured($beneficiary)]);
    }

    /**
     * Save a new payment proof as a pending record.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:9999999'],
            'transfer_reference' => ['nullable', 'string', 'max:100'],
            'receipt_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096'],
        ]);

        $path = null;
        try {
            DB::transaction(function () use ($request, $data, &$path) {
                $booking = $this->payableBooking($request, $data['booking_id'], true);
                if ($this->hasPending($booking)) {
                    $this->fail('receipt_image', __('payments.already_pending'));
                }
                if (!PlatformSetting::cliqConfigured()) {
                    $this->fail('receipt_image', 'CliQ is temporarily unavailable. Contact support for help with your deposit.');
                }
                if (Payment::referenceInUse($data['transfer_reference'] ?? null)) {
                    $this->fail('transfer_reference', __('payments.reference_used'));
                }

                $path = $request->file('receipt_image')->store('receipts', 'local');
                abort_unless($path, 503, 'Could not save the receipt.');

                Payment::create([
                    'booking_id' => $booking->id,
                    'purpose' => Payment::PURPOSE_DEPOSIT,
                    'amount' => $data['amount'],
                    'transfer_reference' => $data['transfer_reference'] ?? null,
                    'receipt_image' => $path,
                    'status' => Payment::STATUS_PENDING,
                ]);

                User::where('role', 'admin')->where('is_active', true)->select('id')->each(
                    fn (User $admin) => MoveItNotification::bookingEvent($admin->id, $booking, 'A payment proof is ready for review.')
                );
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            if ($error instanceof UniqueConstraintViolationException) {
                $this->fail('transfer_reference', __('payments.reference_used'));
            }
            throw $error;
        }

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

        $booking = $query->findOrFail($bookingId);
        abort_if($booking->deposit_due_at?->isPast(), 410, 'The deposit deadline has passed.');

        return $booking;
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
