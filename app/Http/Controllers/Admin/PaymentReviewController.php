<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MoveItNotification;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Str;
use App\Models\ScanSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
/**
 * Admin review of CliQ payment proofs.
 */
class PaymentReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', Payment::STATUS_PENDING);
        $status = in_array($status, ['pending', 'approved', 'rejected', 'all'], true) ? $status : Payment::STATUS_PENDING;
        $search = $request->query('q');
        $search = is_string($search) ? trim(substr($search, 0, 100)) : '';
        $matches = Payment::query()->when($search !== '', function ($query) use ($search) {
            $query->where(function ($fields) use ($search) {
                $fields->where('transfer_reference', 'like', '%'.$search.'%');
                if (ctype_digit($search) && strlen($search) <= 18) {
                    $fields->orWhere('booking_id', $search);
                }
            });
        });

        $counts = (clone $matches)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $overdueOnly = $status === Payment::STATUS_PENDING && $request->boolean('overdue');
        $overdueBookings = fn ($query) => $query->where('deposit_due_at', '<', now());
        $overdueCount = (clone $matches)->where('status', Payment::STATUS_PENDING)
            ->whereHas('booking', $overdueBookings)->count();

        $payments = $matches->with('booking.customer')
            ->when($overdueOnly, fn ($query) => $query->whereHas('booking', $overdueBookings))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest('id')->paginate(10)->withQueryString();

        return view('admin.payments.index', compact('payments', 'status', 'search', 'counts', 'overdueOnly', 'overdueCount'));
    }

    public function show(string $id)
    {
        $payment = Payment::with(['booking.customer', 'reviewer'])->findOrFail($id);

        $approvedTotal = $payment->booking->payments()
            ->where('purpose', Payment::PURPOSE_DEPOSIT)
            ->where('status', Payment::STATUS_APPROVED)
            ->sum('amount');

        return view('admin.payments.show', compact('payment', 'approvedTotal'));
    }

    /**
     * Approve the amount that really arrived; confirm the booking once the deposit is covered.
     */
    public function approve(Request $request, string $id)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:9999999'],
        ]);

        DB::transaction(function () use ($request, $id, $data) {
            $payment = Payment::lockForUpdate()->findOrFail($id);
            $booking = Booking::lockForUpdate()->findOrFail($payment->booking_id);

            // A repeated click or request finds the payment already reviewed and changes nothing.
            if ($payment->status !== Payment::STATUS_PENDING) {
                $this->fail(__('payments.already_reviewed'));
            }

            if ($booking->status !== 'awaiting_deposit') {
                $this->fail(__('payments.booking_not_awaiting'));
            }

            if ($booking->deposit_amount === null) {
                $this->fail(__('payments.no_deposit_amount'));
            }

            if (Payment::referenceInUse($payment->transfer_reference, $payment->id)) {
                $this->fail(__('payments.reference_used'));
            }

            $payment->update([
                'status' => Payment::STATUS_APPROVED,
                'amount' => $data['amount'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $paid = $booking->payments()
                ->where('purpose', Payment::PURPOSE_DEPOSIT)
                ->where('status', Payment::STATUS_APPROVED)
                ->sum('amount');

            // Compare in thousandths of a dinar to avoid float noise.
            if ((int) round($paid * 1000) >= (int) round($booking->deposit_amount * 1000)) {
                $booking->update(['status' => 'confirmed']);
                if ($booking->driver) {
                    MoveItNotification::bookingEvent($booking->driver->user_id, $booking, 'The deposit was approved. The booking is confirmed.');
                }
            }
            MoveItNotification::bookingEvent($booking->customer_id, $booking, 'Your payment proof was approved.');
        });

        return redirect()->route('admin.payments.index')
            ->with('success', __('payments.approved'));
    }

    public function reject(Request $request, string $id)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $id, $data) {
            $payment = Payment::lockForUpdate()->findOrFail($id);

            if ($payment->status !== Payment::STATUS_PENDING) {
                $this->fail(__('payments.already_reviewed'));
            }

            $payment->update([
                'status' => Payment::STATUS_REJECTED,
                'rejection_reason' => $data['rejection_reason'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
            MoveItNotification::bookingEvent($payment->booking->customer_id, $payment->booking, 'Your payment proof was rejected.');
        });

        return redirect()->route('admin.payments.index')
            ->with('success', __('payments.rejected'));
    }

    private function fail(string $message): void
    {
        throw ValidationException::withMessages(['payment' => $message]);
    }
}
