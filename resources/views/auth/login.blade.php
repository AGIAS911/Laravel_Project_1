@extends('layouts.auth')

@section('title', 'Log in')

@section('content')
    <h1>Log in</h1>
    <p class="muted">Log in to manage your bookings.</p>

    @if (session('status'))
        <p class="alert info" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login', absolute: false) }}">
        @csrf
        <div class="field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com" @error('email') aria-describedby="login-email-error" @enderror @error('email') aria-invalid="true" @enderror>
            @error('email') <p id="login-email-error" class="field-error">{{ $message }}</p> @enderror
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" @error('password') aria-describedby="login-password-error" @enderror @error('password') aria-invalid="true" @enderror>
            @error('password') <p id="login-password-error" class="field-error">{{ $message }}</p> @enderror
        </div>
        <div class="row between auth-remember">
            <label class="row sm"><input type="checkbox" name="remember" @checked(old('remember'))> Remember me</label>
            <a class="link sm" href="{{ route('password.request') }}">Forgot your password?</a>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Log in <span class="dot"></span></button>
    </form>
    <p class="center muted sm mt">Don't have an account? <a class="link" href="{{ route('register') }}">Create an account</a></p>
@endsection
