@extends('layouts.auth')

@section('title', 'Choose a new password')

@section('content')
    <h1>Choose a new password</h1>
    <p class="muted" style="margin:6px 0 24px">Enter and confirm your new password.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            @error('email')
                <p class="auth-error sm" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            @error('password')
                <p class="auth-error sm" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Reset password <span class="dot"></span></button>
    </form>
@endsection
