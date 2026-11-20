<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller
{
    public function index()
    {
        $metrics = [
            'bookings' => Booking::count(),
            'receipts' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'drivers' => DriverProfile::where('status', 'pending')->count(),
            'commission' => Booking::where('status', 'completed')->sum('commission_amount'),
        ];

        $today = now('Asia/Amman')->startOfDay();
        $days = collect(range(6, 0))->map(fn ($offset) => $today->copy()->subDays($offset));
        $dailyQuery = Booking::query()
            ->where('created_at', '>=', $days->first()->copy()->utc())
            ->where('created_at', '<', $today->copy()->addDay()->utc());
        foreach ($days as $index => $day) {
            $dailyQuery->selectRaw("COUNT(CASE WHEN created_at >= ? AND created_at < ? THEN 1 END) AS day_$index", [
                $day->copy()->utc(), $day->copy()->addDay()->utc(),
            ]);
        }
        $counts = $dailyQuery->toBase()->first();
        $activity = $days->map(fn ($day, $index) => [
            'label' => $day->format('D'),
            'date' => $day->format('d M Y'),
            'count' => (int) $counts->{"day_$index"},
        ]);
        $expiredWithProof = Booking::where('status', 'awaiting_deposit')
            ->where('deposit_due_at', '<=', now())
            ->whereHas('payments', fn ($payments) => $payments->where('status', Payment::STATUS_PENDING))
            ->count();
        $latestBookings = Booking::with(['customer', 'driver.user'])->latest('id')->limit(5)->get();

        return view('admin.workspace.index', compact('metrics', 'activity', 'expiredWithProof', 'latestBookings'));
    }

    public function reports(Request $request)
    {
        $groups = Booking::selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')->orderByDesc('total')->get();
        $approvedDeposits = Payment::where('status', 'approved')->where('purpose', 'deposit')->sum('amount');
        $completed = Booking::where('status', 'completed');
        $recordedCommission = (clone $completed)->sum('commission_amount');
        $search = $this->reportSearch($request);
        $completedBookings = $this->reportRows($search)->paginate(15)->withQueryString();

        return view('admin.workspace.reports', compact('groups', 'approvedDeposits', 'recordedCommission', 'completedBookings', 'search'));
    }

    public function exportReports(Request $request)
    {
        $rows = $this->reportRows($this->reportSearch($request));

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Booking ID', 'Total (JOD)', 'Locked commission (JOD)'], escape: '');
            foreach ($rows->cursor() as $booking) {
                fputcsv($output, [$booking->id, $booking->total_price, $booking->commission_amount], escape: '');
            }
            fclose($output);
        }, 'moveit-commissions.csv', ['Content-Type' => 'text/csv']);
    }

    private function reportSearch(Request $request): string
    {
        $search = $request->query('q');

        return is_string($search) ? trim(substr($search, 0, 24)) : '';
    }

    private function reportRows(string $search): \Illuminate\Database\Eloquent\Builder
    {
        return Booking::where('status', 'completed')
            ->when($search !== '', fn ($query) => $query->where('id', ctype_digit($search) && strlen($search) <= 18 ? $search : 0))
            ->latest('id');
    }

    public function settings()
    {
        return view('admin.workspace.settings', ['settings' => PlatformSetting::values()]);
    }

    public function updateSettings(Request $request)
    {
        $values = $request->validate([
            'deposit_percent' => 'required|numeric|decimal:0,2|between:0.01,100',
            'commission_percent' => 'required|numeric|decimal:0,2|between:0,100',
            'deposit_minutes' => 'required|integer|min:1|max:10080',
            'clik_name' => 'required|string|max:100',
            'clik_id' => 'required|string|max:100',
        ]);
        PlatformSetting::upsert([['id' => 1, 'settings' => json_encode($values, JSON_THROW_ON_ERROR)]], ['id'], ['settings']);

        return back()->with('success', 'Platform settings saved.');
    }

    public function inbox()
    {
        return view('admin.workspace.inbox', ['messages' => DB::table('contact_messages')->latest('id')->paginate(20)]);
    }
}
