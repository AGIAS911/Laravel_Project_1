@extends('layouts.dashboard')

@section('title', 'New booking | MoveIt')
@section('page-title', 'How would you like to move?')
@section('page-description', 'Pick one option to start your booking.')

@section('content')
    <form class="booking-method-form" method="POST" action="{{ route('booking.store') }}">
        @csrf

        @error('booking_method')
            <p class="alert alert-danger" id="booking-method-error" role="alert">{{ $message }}</p>
        @enderror

        <fieldset @error('booking_method') aria-describedby="booking-method-error" @enderror>
            <legend class="visually-hidden">How to start your booking</legend>
            <div class="booking-method-grid">
                @foreach ([
                    ['quick', 'Choose a vehicle size', 'For when you already know how much space you need.'],
                    ['manual', 'Add furniture manually', 'Make a list of items and get a vehicle size recommendation.'],
                    ['scan', 'Scan with your phone', 'Use your camera to detect furniture, then review the list.'],
                ] as [$method, $heading, $description])
                    <label class="booking-method-option">
                        <input type="radio" name="booking_method" value="{{ $method }}" required
                            aria-labelledby="method-{{ $method }}-title" aria-describedby="method-{{ $method }}-description"
                            @checked(old('booking_method') === $method)>
                        <span class="booking-method-card">
                            <span class="booking-method-art" aria-hidden="true">
                                @switch($method)
                                    @case('quick')
                                        <img class="booking-method-truck" src="{{ asset('landing/img/driver-vehicle-truck.svg') }}" alt="" width="220" height="112">
                                        @break

                                    @case('manual')
                                        <span class="booking-method-list">
                                            <span class="booking-method-list-row"><i class="fa-solid fa-couch"></i><span>1</span></span>
                                            <span class="booking-method-list-row"><i class="fa-solid fa-chair"></i><span>2</span></span>
                                            <span class="booking-method-list-add"><i class="fa-solid fa-plus"></i></span>
                                        </span>
                                        @break

                                    @case('scan')
                                        <span class="booking-method-phone">
                                            <span class="booking-method-camera">
                                                <img src="{{ asset('landing/img/hero.jpg') }}" alt="" width="1280" height="720">
                                            </span>
                                            <span class="booking-method-shutter"></span>
                                        </span>
                                        @break
                                @endswitch
                            </span>
                            <span class="booking-method-copy">
                                <strong id="method-{{ $method }}-title">{{ $heading }}</strong>
                                <span id="method-{{ $method }}-description">{{ $description }}</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="booking-method-actions">
            <button class="btn btn-primary product-action" type="submit">
                Start booking <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    </form>
@endsection
