<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;

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
     * @return Renderable
     */
    public function index()
    {
        $dashboardLayout = match (auth()->user()->role) {
            'customer' => 'layouts.customer',
            'driver' => 'layouts.driver',
            'admin' => 'layouts.admin',
            default => 'layouts.dashboard',
        };

        return view('home', compact('dashboardLayout'));
    }
}
