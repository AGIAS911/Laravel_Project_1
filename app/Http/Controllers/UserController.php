<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\DriverApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $pendingDrivers = User::where('role', 'driver')->where('is_active', false)->get();
        $activeDrivers = User::where('role', 'driver')->where('is_active', true)->get();
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

        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'is_active' => true, 
        ]);

        return redirect()->back()->with('success', 'User added successfully.');
    }

    public function update(Request $request, User $user)
    {
        if ($request->has('approve_driver')) {
            $request->validate([
                'approve_driver' => ['accepted'],
            ]);

            abort_unless($user->role === 'driver', 404);

            if (! $user->is_active) {
                $user->update([
                    'is_active' => true,
                ]);

                $user->notify(new DriverApprovedNotification());
            }

            return redirect()->back()->with('success', 'Driver approved.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:150',
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