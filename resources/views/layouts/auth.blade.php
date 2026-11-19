@extends('layouts.moveit-public')

@section('title', 'Account')

@section('page')
    <div class="auth">
        <aside class="auth-side">
            <video autoplay muted loop playsinline aria-hidden="true" poster="{{ asset('landing/img/hero.jpg') }}">
                <source src="{{ asset('landing/video/hero.mp4') }}" type="video/mp4">
            </video>
            <h2>@yield('auth-heading', 'Move your furniture with confidence and clarity')</h2>
            <p style="color:#cbd5e1;margin-top:10px">Smart inventory, clear pricing and an approved driver.</p>
        </aside>
        <main class="auth-main">
            <div class="auth-box">
                <a href="{{ url('/') }}" class="logo" aria-label="MoveIt home"><span class="moveit-brand">MOVE<span>IT</span></span></a>
                @yield('content')
            </div>
        </main>
    </div>
@endsection
