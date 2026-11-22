@extends('layouts.moveit-public')

@section('page')
    <div class="auth">
        <aside class="auth-side" aria-label="About MoveIt">
            <img src="{{ asset('landing/img/room.jpg') }}" alt="" width="800" height="600">
            <div class="product-auth-cover">
                <h2>Plan your move.</h2>
                <p>See your moving price, pay your deposit and keep up with your booking from one place.</p>
            </div>
        </aside>
        <main class="auth-main">
            <div class="auth-box">
                <a href="{{ url('/') }}" class="logo" aria-label="MoveIt home"><span class="moveit-brand">MOVE<span>IT</span></span></a>
                @yield('content')
            </div>
        </main>
    </div>
@endsection
