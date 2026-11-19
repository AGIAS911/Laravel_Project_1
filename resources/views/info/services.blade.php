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
        <div class="grid g3">
            @forelse ($services as $service)
                @php($icon = match ($service->type) {
                    'workers' => 'fa-people-carry-box',
                    'packing' => 'fa-box-open',
                    'boxes' => 'fa-boxes-stacked',
                    'assembly' => 'fa-screwdriver-wrench',
                    default => 'fa-truck-moving',
                })
                <article class="s-card">
                    <div class="thumb"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></div>
                    <h3>{{ $service->name }}</h3>
                    <p class="muted" style="margin:0 10px 10px">{{ $service->description }}</p>
                    <div class="more"><b class="price-tag">{{ $service->price }} JOD @if ($service->unit !== 'fixed') / {{ str($service->unit)->replace('_', ' ') }} @endif</b></div>
                </article>
            @empty
                <p class="muted">No services are listed yet.</p>
            @endforelse
        </div>
        <div class="mt">{{ $services->links('info.pagination') }}</div>
    </div>
</section>
@endsection
