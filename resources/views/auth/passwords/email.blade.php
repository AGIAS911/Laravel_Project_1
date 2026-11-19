@extends('layouts.auth')

@section('title', 'Reset password')

@section('content')
    <h1>Reset password</h1>
    <p class="muted" style="margin:6px 0 24px">Enter your email to receive a temporary link.</p>

    @if (session('status'))
        <p class="alert ok" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')
                <p id="email-error" class="auth-error sm" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send link <span class="dot"></span></button>
    </form>

    <p class="center mt"><a class="link" href="{{ route('login') }}">Back to login</a></p>
@endsection
