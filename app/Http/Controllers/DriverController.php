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
            if ($driver->license_image) {
                Storage::disk('public')->delete($driver->license_image);
            }
            $driverData['license_image'] = $request->file('license_image')->store('licenses', 'public');
            $driverData['status'] = 'pending';
        }

        $driver->update($driverData);

        return redirect()->route('driver.profile.show')->with('success', 'Driver profile updated successfully.');
    }

    public function destroy()
    {
        $user = User::findOrFail(Auth::id());
        $driver = $user->driverProfile;

        if ($driver && $driver->license_image) {
            Storage::disk('public')->delete($driver->license_image);
        }

        Auth::logout();
        $user->delete();

        return redirect('/')->with('success', 'Account deleted successfully.');
    }
}
