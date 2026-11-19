<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $profile = $user?->role === 'driver' ? $user->driverProfile : null;

        if ($user && (!$user->is_active || ($profile && $profile->status !== 'approved'))) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($profile?->status === 'pending') {
                return redirect()->route('driver.application.pending');
            }

            return redirect('/login')->withErrors([
                'email' => 'Your account is being modified or has been suspended by the administration.'
            ]);
        }

        return $next($request);
    }
}