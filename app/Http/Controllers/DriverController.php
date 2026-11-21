<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function dashboard(Request $request)
    {
        $driver = $request->user()->driverProfile;

        if (!$driver) {
            return redirect()->route('driver.profile.edit');
        }
        $counts = $driver->bookings()->whereIn('status', ['pending', 'confirmed', 'in_progress', 'completed'])
            ->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        $upcoming = $driver->bookings()
            ->with(['customer:id,name', 'vehicle:id,name'])
            ->where(fn ($query) => $query->where('status', 'in_progress')
                ->orWhere(fn ($confirmed) => $confirmed->where('status', 'confirmed')
                    ->where(fn ($scheduled) => $scheduled->where('scheduled_start_at', '>=', now())
                        ->orWhere('scheduled_end_at', '>=', now()))))
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_start_at')
            ->orderBy('id')
            ->limit(4)
            ->get();

        return view('driver.dashboard', compact('driver', 'counts', 'upcoming'));
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $driver = $user->driverProfile;

        if (!$driver) {
            return redirect()->route('driver.profile.edit');
        }

        $hasLicensePhoto = $driver->license_image && Storage::disk('local')->exists($driver->license_image);

        return view('driver.profile.show', compact('driver', 'user', 'hasLicensePhoto'));
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $driver = $user->driverProfile;

        return view('driver.profile.edit', compact('driver', 'user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $driver = $user->driverProfile;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => $request->filled('password') ? ['required', 'current_password'] : ['nullable'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'license_number' => ['required', 'string', 'max:100'],
            'license_image' => [
                !$driver || $driver->license_number !== $request->input('license_number') ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],
        ]);

        $image = null;
        if ($request->hasFile('license_image')) {
            $image = $request->file('license_image')->store('licenses', 'local');
            abort_unless($image, 503, 'Could not save the driver license.');
        }

        try {
            $previousImage = DB::transaction(function () use ($user, $validated, $image) {
                $account = User::lockForUpdate()->findOrFail($user->id);
                abort_unless($account->is_active && $account->role === 'driver', 403);

                $accountData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ];
                if ($account->email !== $validated['email']) {
                    $accountData['email_verified_at'] = null;
                }
                if (!empty($validated['password'])) {
                    $accountData['password'] = Hash::make($validated['password']);
                }
                $account->forceFill($accountData)->save();

                $profile = $account->driverProfile()->firstOrNew();
                $previousImage = $profile->license_image;

                $profileData = [
                    'license_number' => $validated['license_number'],
                    'status' => $profile->exists ? $profile->status : 'approved',
                    'status_reason' => $image ? null : $profile->status_reason,
                ];
                if ($image) {
                    $profileData['license_image'] = $image;
                }
                $profile->forceFill($profileData)->save();

                return $image ? $previousImage : null;
            });
        } catch (\Throwable $error) {
            if ($image) {
                Storage::disk('local')->delete($image);
            }
            throw $error;
        }

        if ($previousImage) {
            Storage::disk('local')->delete($previousImage);
        }

        if (!empty($validated['password'])) {
            $request->session()->regenerate();
        }

        return redirect()->route('driver.profile.show')->with('success', 'Driver profile updated successfully.');
    }

    public function destroy()
    {
        $user = User::findOrFail(Auth::id());
        Auth::logout();
        $user->forceFill(['is_active' => false])->save();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/')->with('success', 'Account disabled successfully.');
    }

    public function license(Request $request, DriverProfile $driverProfile)
    {
        abort_unless($request->user()->role === 'admin' || $driverProfile->user_id === $request->user()->id, 404);
        abort_unless($driverProfile->license_image && Storage::disk('local')->exists($driverProfile->license_image), 404);

        return Storage::disk('local')->response($driverProfile->license_image, null, ['Cache-Control' => 'private, no-store']);
    }
}
