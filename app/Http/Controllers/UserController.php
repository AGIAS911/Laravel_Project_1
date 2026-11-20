<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\User;
use App\Notifications\DriverApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->routeIs('admin.driver-approvals')) {
            $role = $request->query('role');
            $role = in_array($role, ['customer', 'driver'], true) ? $role : null;

            $users = User::with('driverProfile')
                ->when($role, fn ($query) => $query->where('role', $role))
                ->latest('id')
                ->paginate(20)
                ->withQueryString();

            return view('admin.users.list', compact('users', 'role'));
        }

        $tabs = ['pending' => 'Pending approval', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended'];
        $status = $request->query('status');
        $status = is_string($status) && isset($tabs[$status]) ? $status : 'pending';
        $drivers = User::where('role', 'driver')
            ->whereHas('driverProfile', fn ($profiles) => $profiles->where('status', $status))
            ->with(['driverProfile' => fn ($profiles) => $profiles->withCount('vehicles')])
            ->latest('id')->paginate(20)->withQueryString();
        $pendingCount = DriverProfile::where('status', 'pending')->count();

        return view('admin.users.index', compact('drivers', 'tabs', 'status', 'pendingCount'));
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'license_number' => [
                Rule::requiredIf($user->role === 'driver' && !$user->driverProfile && $request->hasFile('license_image')),
                'nullable', 'string', 'max:100',
            ],
            'license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $newImage = null;
        if ($user->role === 'driver' && $request->hasFile('license_image')) {
            $newImage = $request->file('license_image')->store('licenses', 'local');
            abort_unless($newImage, 503, 'Could not save the driver license.');
        }

        try {
            $previousImage = DB::transaction(function () use ($user, $validatedData, $newImage) {
                $userData = ['name' => $validatedData['name']];

                if (array_key_exists('email', $validatedData)) {
                    $userData['email'] = $validatedData['email'];
                }
                if (array_key_exists('phone', $validatedData)) {
                    $userData['phone'] = $validatedData['phone'];
                }

                $user->update($userData);

                if ($user->role !== 'driver') {
                    return null;
                }

                $hasLicenseNumber = !empty($validatedData['license_number']);
                if (!$hasLicenseNumber && !$newImage) {
                    return null;
                }

                $profile = $user->driverProfile()->lockForUpdate()->firstOrNew();
                $oldImage = $profile->license_image;

                if ($hasLicenseNumber) {
                    $profile->license_number = $validatedData['license_number'];
                }
                if ($newImage) {
                    $profile->license_image = $newImage;
                }
                if (!$profile->exists) {
                    $profile->status = $user->is_active ? 'approved' : 'pending';
                }

                $profile->save();

                return $newImage ? $oldImage : null;
            });
        } catch (\Throwable $error) {
            if ($newImage) {
                Storage::disk('local')->delete($newImage);
            }
            throw $error;
        }

        if ($previousImage) {
            Storage::disk('local')->delete($previousImage);
        }

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
            $user = User::lockForUpdate()->findOrFail($user->id);
            $driver = $user->driverProfile()->lockForUpdate()->firstOrFail();
            $changed = $driver->status !== $status || $driver->status_reason !== $reason;
            $driver->forceFill(['status' => $status, 'status_reason' => $reason])->save();
            $user->forceFill(['is_active' => $status === 'approved'])->save();
            if ($changed && $status === 'approved') {
                $user->notify(new DriverApprovedNotification());
            } elseif ($changed) {
                $user->notifications()->create([
                    'type' => 'driver_status',
                    'title' => 'Driver account updated',
                    'message' => $reason,
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