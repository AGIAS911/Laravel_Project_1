@extends('layouts.info')

@section('title', 'MoveIt services')

@section('content')
<div class="container">
    <section class="page-hero">
        <div class="crumbs"><a href="{{ url('/') }}">Home</a> / MoveIt services</div>
        <h1>MoveIt services</h1>
        <p class="lead mt-s">One basic transport service per booking, with optional extras and custom quantities.</p>
    </section>
</div>
<section class="section">
    <div class="container">
        <div class="grid g2 product-catalog">
            @forelse ($services as $service)
                <article class="s-card">
                    <div class="thumb"><x-service-icon :type="$service->type" /></div>
                    <h3>{{ $service->name }}</h3>
                    <p class="muted">{{ $service->description }}</p>
                    <div class="more"><b class="price-tag">{{ $service->price }} JOD @if ($service->unit !== 'fixed') / {{ str($service->unit)->replace('_', ' ') }} @endif</b></div>
                </article>
            @empty
                <div class="panel"><p>No services are listed yet.</p><a class="link" href="{{ route('info.contact') }}">Ask about your move</a></div>
            @endforelse
        </div>
        <div class="mt">{{ $services->links('info.pagination') }}</div>
    </div>
</section>
@endsection
