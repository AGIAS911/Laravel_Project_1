@extends('layouts.dashboard')

@section('title', 'New booking | MoveIt')
@section('page-title', 'How would you like to move?')
@section('page-description', 'Choose the option that works best for you.')

@section('content')
    <div class="booking-methods">
        @foreach ([
            ['quick', 'Choose a vehicle type', 'I already know the vehicle size I need.', 'Choose vehicle size', 'driver-vehicle-truck.svg', 'fa-truck'],
            ['manual', 'Build your inventory', 'Add furniture manually and find a suitable vehicle.', 'Add items manually', 'hero.jpg', 'fa-boxes-stacked'],
            ['scan', 'Scan your furniture', 'Use your phone camera to build your furniture inventory.', 'Scan with phone', 'room.jpg', 'fa-mobile-screen'],
        ] as [$method, $heading, $description, $action, $photo, $icon])
            <section @class(['booking-method-card', 'has-vehicle-art' => $method === 'quick'])>
                <img src="{{ asset('landing/img/'.$photo) }}" alt="" width="512" height="256">
                <div>
                    <span class="booking-method-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <h2>{{ $heading }}</h2>
                    <p>{{ $description }}</p>
                    <form method="POST" action="{{ route('booking.store') }}">
                        @csrf
                        <input type="hidden" name="booking_method" value="{{ $method }}">
                        <button class="btn {{ $method === 'quick' ? 'btn-primary' : 'btn-outline-secondary' }}" type="submit">
                            {{ $action }} <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </section>
        @endforeach
    </div>
@endsection
