@extends('layouts.moveit-public')

@section('title', 'Log in')

@section('page')
<div class="auth">
    <aside class="auth-side">
        <video autoplay muted loop playsinline aria-hidden="true" poster="{{ asset('landing/img/hero.jpg') }}">
            <source src="{{ asset('landing/video/hero.mp4') }}" type="video/mp4">
        </video>
        <h2>Move your furniture<br>with confidence and clarity</h2>
        <p style="color:#cbd5e1;margin-top:10px">Smart inventory, clear pricing and an approved driver.</p>
    </aside>
    <main class="auth-main">
        <div class="auth-box">
            <a href="{{ url('/') }}" class="logo" aria-label="MoveIt home"><span class="moveit-brand">MOVE<span>IT</span></span></a>
            <h1>Welcome back</h1>
            <p class="muted" style="margin:6px 0 24px">Log in to manage your bookings.</p>

            @if (session('status'))
                <p class="alert info" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login', absolute: false) }}">
                @csrf
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com">
                    @error('email') <p role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password') <p role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="row between" style="margin-bottom:18px">
                    <label class="row sm"><input type="checkbox" name="remember" @checked(old('remember'))> Remember me</label>
                    <a class="link sm" href="{{ route('password.request') }}">Forgot your password?</a>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Log in <span class="dot"></span></button>
            </form>
            <p class="center muted sm mt">Don't have an account? <a class="link" href="{{ route('register') }}">Create an account</a></p>
        </div>
    </main>
</div>
@endsection
