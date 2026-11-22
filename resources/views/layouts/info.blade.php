@extends('layouts.moveit-public')

@section('page')
@php
    $user = auth()->user();
    $accountRoute = match ($user?->role) {
        'admin' => 'admin.dashboard',
        'driver' => 'driver.dashboard',
        'customer' => 'home',
        default => 'login',
    };
    $accountLabel = $user ? 'Dashboard' : 'Log in';
    $bookingRoute = $user?->role === 'customer' ? 'booking.create' : ($user ? $accountRoute : 'register');
@endphp
@include('layouts.moveit-public-header')

<main id="info-main" class="product-info-main" tabindex="-1">
    @yield('content')
</main>

@include('layouts.moveit-public-footer')
@endsection
