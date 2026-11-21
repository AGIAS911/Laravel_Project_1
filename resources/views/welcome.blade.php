@extends('layouts.landing')

@section('title', 'MoveIt | Moving made simple.')

@section('content')
@php
    $user = auth()->user();
    $accountRoute = match ($user?->role) {
        'admin' => 'admin.dashboard',
        'driver' => 'driver.dashboard',
        'customer' => 'home',
        default => 'login',
    };
    $accountLabel = $user ? 'Dashboard' : 'Log in';
    $bookingRoute = $user?->role === 'customer' ? 'booking.create' : ($user ? $accountRoute : 'register');
@endphp
<main class="page-shell">
<section class="masthead">
<video class="hero-film" autoplay muted loop playsinline poster="{{ asset('landing/img/hero.jpg') }}" aria-hidden="true"><source src="{{ asset('landing/video/hero.mp4') }}" type="video/mp4"></video>
<header class="landing-nav">
<a class="moveit-brand" href="{{ url('/') }}" aria-label="MoveIt">MOVE<span>IT</span></a>
<nav id="landing-menu" aria-label="Main navigation"><a href="#services">Services</a><a href="{{ route('vehicle-types.index') }}">Vehicles</a><a href="#process">How it works</a><a href="{{ route('info.faq') }}">FAQ</a><a href="{{ route('info.contact') }}">Contact</a><a class="mobile-login" href="{{ route($accountRoute) }}">{{ $accountLabel }}</a></nav>
<button class="menu-trigger" aria-controls="landing-menu" aria-expanded="false" aria-label="Toggle menu">☰</button>
<div class="nav-notch"><a href="{{ route($accountRoute) }}" class="login-link">{{ $accountLabel }}</a><a class="action red" href="{{ route($bookingRoute) }}">Get a quote <span>●</span></a></div>
</header>
<div class="hero-content"><div class="label-line">● &nbsp; THE SMARTER WAY TO MOVE · JORDAN</div><h1>YOUR NEXT MOVE.<br><span>MADE SIMPLE.</span></h1><p>Scan your furniture, find the right vehicle and know your price before you book.</p></div>
<div class="hero-bottom"><div class="intro-notch"><h2>SMART<br><em>FURNITURE</em><br>MOVING</h2><a href="#trust">Discover MoveIt ↗</a></div><div class="hero-options"><a href="{{ route($bookingRoute) }}">Full apartment</a><a href="{{ route($bookingRoute) }}">Single room</a><a href="{{ route($bookingRoute) }}">Large items</a><a href="{{ route($bookingRoute) }}">Office move</a></div></div>
</section>
<section class="trust-section reveal" id="trust">
<div class="trust-visual"><video autoplay muted loop playsinline poster="{{ asset('landing/img/scan.jpg') }}" aria-hidden="true"><source src="{{ asset('landing/video/hero.mp4') }}" type="video/mp4"></video><div class="trust-heading"><span class="micro">01 / BUILT FOR CONFIDENCE</span><h2>WHY YOU CAN<br>TRUST US WITH<br>YOUR MOVE.</h2><a class="action outline" href="{{ route($bookingRoute) }}">Get a free estimate <span>●</span></a></div><span class="scan-tag">● AI INVENTORY SCAN</span></div>
<div class="trust-features"><article><h3>AI-POWERED INVENTORY</h3><p>Scan your room with your phone. Review detected furniture and confirm your inventory.</p></article><article><h3>TRANSPARENT PRICING</h3><p>See delivery costs, additional services and your deposit before sending a request.</p></article><article><h3>VERIFIED DRIVERS & PAYMENTS</h3><p>We verify your CliQ deposit before looking for an approved driver for your move.</p></article></div>
</section>
<section class="services-section" id="services">
<div class="section-intro reveal">
<div class="service-truck-stage" aria-hidden="true">
  <div class="service-truck">
    <div class="truck-cargo"><div class="truck-branding"><span class="moveit-brand truck-logo">MOVE<span>IT</span></span><span class="truck-service">FURNITURE MOVING</span></div><span class="truck-box"></span></div>
    <div class="truck-cab"><span class="truck-door"></span></div>
    <span class="truck-wheel"></span>
    <span class="truck-wheel"></span>
  </div>
</div>
<div class="service-copy">
<span class="micro">02 / OUR SERVICES</span><h2>WHATEVER YOU MOVE,<br><span>WE MAKE IT EASIER.</span></h2><p>Flexible transportation and additional services, tailored to the size of your move.</p></div></div>
<div class="service-track">
<a class="service-tile" href="{{ route('services.index') }}"><img src="{{ asset('landing/img/hero.jpg') }}" alt="Furnished apartment"><h3>FULL APARTMENT MOVES</h3><div>Explore service <span>●</span></div></a>
<a class="service-tile" href="{{ route('services.index') }}"><img src="{{ asset('landing/img/room.jpg') }}" alt="Furniture inventory"><h3>SINGLE ROOM & LARGE ITEMS</h3><div>Explore service <span>●</span></div></a>
<a class="service-tile" href="{{ route('services.index') }}"><img src="{{ asset('landing/img/office.jpg') }}" alt="Interior ready for a move"><h3>OFFICE RELOCATION</h3><div>Explore service <span>●</span></div></a>
<a class="service-tile" href="{{ route('services.index') }}"><img src="{{ asset('landing/img/scan.jpg') }}" alt="Smart furniture detection"><h3>PACKING & ASSEMBLY</h3><div>Explore service <span>●</span></div></a>
</div></section>
<section class="promise-section reveal" id="process"><div class="promise-map"><div class="number one"><b>3</b><p>simple booking<br>steps</p></div><div class="number two"><b>24/7</b><p>booking access</p></div><div class="number three"><b>100%</b><p>price breakdown<br>before booking</p></div><span class="map-caption">DESIGNED FOR MOVES ACROSS JORDAN</span></div><div class="promise-copy"><span class="micro">03 / THE PROCESS</span><h2>WE MAKE SURE<br>EVERY MOVE<br>COUNTS.</h2><ol><li><b>Plan your move.</b> Choose a vehicle size, pickup, destination and appointment. Add or scan furniture if you need a size recommendation.</li><li><b>Review &amp; pay.</b> Check the quote, transfer your deposit via CliQ and upload the receipt.</li><li><b>Meet your driver.</b> We look for a driver after verifying your deposit. Their details appear when they accept.</li></ol><a class="action outline" href="{{ route('info.how-it-works') }}">See how it works <span>●</span></a></div></section>
<section class="bottom-cta reveal"><div><span class="micro">READY WHEN YOU ARE</span><h2>LET'S GET MOVING.</h2></div><a class="action red" href="{{ route($bookingRoute) }}">Start your move <span>●</span></a></section>
<footer class="landing-footer"><a class="moveit-brand" href="{{ url('/') }}" aria-label="MoveIt">MOVE<span>IT</span></a><p>Smart moving. Clear pricing. Trusted connections.</p><div><a href="{{ route('info.contact') }}">Contact</a><a href="{{ route('info.policies') }}">Policies</a><a href="{{ route($accountRoute) }}">{{ $accountLabel }}</a></div><small>© 2026 MoveIt · Prices in JOD</small></footer>
</main>
@endsection
