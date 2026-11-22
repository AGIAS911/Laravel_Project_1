@extends('layouts.moveit-public')

@section('title', 'Too many requests')

@section('page')
    <main class="container product-recovery">
        <a class="moveit-brand" href="{{ url('/') }}" aria-label="MoveIt home">MOVE<span>IT</span></a>
        <section class="panel" aria-labelledby="error-title">
            <h1 id="error-title">Too many requests</h1>
            <p class="muted">Wait a moment, then try again.</p>
            <div class="product-info-actions">
                <a class="btn btn-primary" href="{{ url('/') }}">MoveIt home</a>
                <a class="btn btn-outline" href="{{ route('info.contact') }}">Contact support</a>
            </div>
        </section>
    </main>
@endsection
