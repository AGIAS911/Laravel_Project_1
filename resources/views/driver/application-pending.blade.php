@extends('layouts.auth')

@section('title', 'Application under review')

@section('content')
    <div class="alert info" role="status">
        <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
        <div><strong>Driver application submitted</strong></div>
    </div>

    <h1 class="mt-s">Your application is under review</h1>
    <p class="muted mt-s">The MoveIt team will review your driver application and license. Once approved, sign in to manage your vehicles, requests, and schedule.</p>

    <div class="mt">
        <a class="btn btn-primary btn-block" href="{{ route('login') }}">Back to log in</a>
        <p class="center mt-s"><a class="link" href="{{ url('/') }}">Back to home</a></p>
    </div>
@endsection
