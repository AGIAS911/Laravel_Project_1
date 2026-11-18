@extends('layouts.app')

@section('content')
    <section class="py-5">
        <h1 class="display-4 fw-bold">{{ __('Move with MoveIt') }}</h1>
        <p class="lead">{{ __('Connect with drivers for your next move.') }}</p>

        @auth
            <a class="btn btn-primary" href="{{ route('home') }}">{{ __('Open dashboard') }}</a>
        @else
            <a class="btn btn-primary" href="{{ route('register') }}">{{ __('Create account') }}</a>
        @endauth
    </section>
@endsection
