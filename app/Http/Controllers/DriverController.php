<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    public function show()
    {
        $driver = DriverProfile::where('user_id', Auth::id())
            ->with(['user', 'vehicles', 'bookings'])
            ->firstOrFail();

        return view('driver.profile.show', compact('driver'));
    }

    public function edit()
    {
        $driver = DriverProfile::where('user_id', Auth::id())
            ->with('user')
            ->firstOrFail();

        return view('driver.profile.edit', compact('driver'));
    }

    public function update(Request $request)
    {
        $driver = DriverProfile::where('user_id', Auth::id())->firstOrFail();



        $validated = $request->validate([
            'license_number' => ['required', 'string', 'max:100'],
            'license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $driverData = [
            'license_number' => $validated['license_number'],
        ];

        if ($request->hasFile('license_image')) {
            $driverData['license_image'] = $request->file('license_image')->store('licenses', 'local');
            abort_unless($driverData['license_image'], 503, 'Could not save the driver license.');
            $driverData['status'] = 'pending';
        }

        $previousImage = $driver->license_image;
        $driver->update($driverData);
        if (isset($driverData['license_image']) && $previousImage) {
            Storage::disk('local')->delete($previousImage);
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
