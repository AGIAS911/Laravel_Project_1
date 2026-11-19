<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        return view('driver.dashboard', compact('driver', 'counts'));
    }

    public function show(Request $request)
    {
        $driver = $request->user()->driverProfile;

        return $driver
            ? view('driver.profile.show', compact('driver'))
            : redirect()->route('driver.profile.edit');
    }

    public function edit(Request $request)
    {
        $driver = $request->user()->driverProfile;

        return view('driver.profile.edit', compact('driver'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $driver = $user->driverProfile;

        $validated = $request->validate([
            'license_number' => ['required', 'string', 'max:100'],
            'license_image' => [
                !$driver || $driver->license_number !== $request->input('license_number') ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],
        ]);

        if (!$request->hasFile('license_image')) {
            return redirect()->route('driver.profile.show');
        }

        $image = $request->file('license_image')->store('licenses', 'local');
        abort_unless($image, 503, 'Could not save the driver license.');

        try {
            $previousImage = DB::transaction(function () use ($user, $validated, $image) {
                $account = User::lockForUpdate()->findOrFail($user->id);
                abort_unless($account->is_active && $account->role === 'driver', 403);

                $profile = $account->driverProfile()->firstOrNew();
                $previousImage = $profile->license_image;

                $profile->forceFill([
                    'license_number' => $validated['license_number'],
                    'license_image' => $image,
                    'status' => $profile->exists ? $profile->status : 'approved',
                    'status_reason' => null,
                ])->save();

                return $previousImage;
            });
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($image);
            throw $error;
        }

        if ($previousImage) {
            Storage::disk('local')->delete($previousImage);
        }

        return redirect()->route('driver.profile.show')->with('success', 'Driver license updated successfully.');
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
