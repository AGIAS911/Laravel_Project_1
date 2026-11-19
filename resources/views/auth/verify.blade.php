@extends('layouts.auth')

@section('title', 'Verify your email')

@section('content')
    <h1>Verify your email</h1>
    <p class="muted mt-s">Check your inbox for a verification link.</p>

    @if (session('resent'))
        <p class="alert ok mt" role="status">A new verification link was sent to your email address.</p>
    @endif

    <form class="mt" method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-block">Send another link <span class="dot"></span></button>
    </form>
@endsection
