<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }


    protected function authenticated(Request $request, $user)
    {
        $profile = $user->role === 'driver' ? $user->driverProfile : null;

        if (!$user->is_active || ($profile && $profile->status !== 'approved')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($profile?->status === 'pending') {
                return redirect()->route('driver.application.pending');
            }

            return redirect('/login')->withErrors([
                'email' => $profile?->status_reason ?: 'Your account is pending admin approval or inactive.',
            ]);
        }

        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        if ($user->role === 'driver') {
            return redirect()->route($profile ? 'driver.dashboard' : 'driver.profile.edit');
        }

        return redirect('/home');
    }
}
