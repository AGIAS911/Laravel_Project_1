<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $counts = [];
        $recentBookings = collect();
        $recentNotifications = collect();
        if ($user->role === 'customer') {
            $counts = $user->bookings()->selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')->pluck('total', 'status')->all();
            $recentBookings = $user->bookings()->latest()->limit(5)->get();
            $recentNotifications = $user->notifications()->latest()->limit(3)->get();
        }

        return view('home', compact('counts', 'recentBookings', 'recentNotifications'));
    }
}
