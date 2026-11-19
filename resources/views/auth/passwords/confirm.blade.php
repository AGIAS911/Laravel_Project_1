@extends('layouts.auth')

@section('title', 'Confirm password')

@section('content')
    <h1>Confirm your password</h1>
    <p class="muted mt-s">Enter your password to continue.</p>

    <form class="mt" method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                @error('password') aria-invalid="true" aria-describedby="confirm-password-error" @enderror>
            @error('password')
                <p id="confirm-password-error" class="auth-error sm" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-block">Confirm password <span class="dot"></span></button>
    </form>

    @if (Route::has('password.request'))
        <p class="center mt"><a class="link" href="{{ route('password.request') }}">Forgot your password?</a></p>
    @endif
@endsection
