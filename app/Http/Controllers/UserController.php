<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\DriverApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $pendingDrivers = User::with('driverProfile')->where('role', 'driver')->where('is_active', false)->get();
        $activeDrivers = User::with('driverProfile')->where('role', 'driver')->where('is_active', true)->get();
        $customers = User::where('role', 'customer')->get();

        return view('admin.users.index', compact('pendingDrivers', 'activeDrivers', 'customers'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,driver,customer',
        ]);

        $user = new User([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $user->role = $validatedData['role'];
        $user->is_active = true;
        $user->save();

        return redirect()->back()->with('success', 'User added successfully.');
    }

    public function update(Request $request, User $user)
    {
        if ($request->has('approve_driver')) {
            return $this->updateDriverStatus($user, 'approved', null);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $user->update($validatedData);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function reviewDriver(Request $request, User $user)
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected,suspended',
            'reason' => 'required_if:status,rejected,suspended|nullable|string|max:1000',
        ]);

        return $this->updateDriverStatus($user, $data['status'], $data['reason'] ?? null);
    }

    private function updateDriverStatus(User $user, string $status, ?string $reason)
    {
        abort_unless($user->role === 'driver', 404);
        DB::transaction(function () use ($user, $status, $reason) {
            $driver = $user->driverProfile()->lockForUpdate()->firstOrFail();
            $changed = $driver->status !== $status || $driver->status_reason !== $reason;
            $driver->forceFill(['status' => $status, 'status_reason' => $reason])->save();
            $user->forceFill(['is_active' => $status === 'approved'])->save();
            if ($changed && $status === 'approved') {
                $user->notify(new DriverApprovedNotification());
            } elseif ($changed) {
                $user->notifications()->create([
                    'type' => 'driver_status', 'title' => 'Driver account updated', 'message' => $reason,
                ]);
            }
        });

        if (request()->expectsJson()) {
            return response()->json(['status' => $status, 'reason' => $reason]);
        }

        return back()->with('success', 'Driver status updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->bookings()->exists() || $user->driverProfile || $user->reviewedPayments()->exists()) {
            $user->forceFill(['is_active' => false])->save();
        } else {
            $user->delete();
        }

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
