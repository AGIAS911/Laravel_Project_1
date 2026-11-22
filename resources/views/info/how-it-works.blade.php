@extends('layouts.info')

@section('title', 'How it works')

@section('content')
<div class="container">
    <header class="page-hero">
        <h1>How MoveIt works</h1>
        <p class="lead mt-s">Plan a move, review the price and track your booking in one place.</p>
    </header>
</div>
<section class="section">
    <div class="container product-guide">
        <ol class="product-guide-steps">
            @foreach ([
                ['Plan your move', 'Choose a vehicle size directly, or build an inventory by adding items or scanning with your phone. Set your pickup, destination and appointment time.'],
                ['Review and pay the deposit', 'Review your moving price and deposit. Make the transfer through CliQ, then upload your receipt from the booking.'],
                ['Confirm with your driver', 'Once the full deposit is verified, MoveIt looks for a driver. Your booking shows the driver and vehicle when they accept.'],
                ['Review your move', 'After the move is complete, open the booking to leave a rating.'],
            ] as [$title, $description])
                <li><span aria-hidden="true">{{ $loop->iteration }}</span><div><h2>{{ $title }}</h2><p class="muted">{{ $description }}</p></div></li>
            @endforeach
        </ol>
        <aside class="panel product-guide-aside">
            <h2>Ready to plan a move?</h2>
            <p class="muted">Check vehicle sizes or start a booking to see your options.</p>
            <div class="product-info-actions">
                @if (!auth()->check() || auth()->user()->role === 'customer')
                    <a class="btn btn-primary" href="{{ route(auth()->check() ? 'booking.create' : 'register') }}">Start booking</a>
                @endif
                <a class="btn btn-outline" href="{{ route('vehicle-types.index') }}">See vehicle types</a>
            </div>
        </aside>
    </div>
</section>
@endsection
