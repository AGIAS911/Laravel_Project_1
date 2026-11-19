<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $user->forceFill(['is_active' => true])->save();

            if ($user->driverProfile) {
                $user->driverProfile->update(['status' => 'approved']);
            }

            return redirect()->back()->with('success', 'Driver approved.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $user->update($validatedData);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}