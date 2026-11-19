@extends('layouts.info')

@section('title', 'Vehicle types')

@section('content')
<div class="container">
    <section class="page-hero">
        <div class="crumbs"><a href="{{ url('/') }}">Home</a> / Vehicle types</div>
        <h1>Vehicle types</h1>
        <p class="lead mt-s">Choose a vehicle based on your furniture size. The driver confirms capacity when accepting.</p>
    </section>
</div>
<section class="section">
    <div class="container">
        <div class="grid g4">
            @forelse ($vehicleTypes as $vehicleType)
                <article class="v-card">
                    <div class="veh">
                        @if ($vehicleType->image)
                            <img src="{{ asset($vehicleType->image) }}" alt="">
                        @else
                            <i class="fa-solid fa-truck-moving" aria-hidden="true"></i>
                        @endif
                    </div>
                    <h3>{{ $vehicleType->name }}</h3>
                    <p class="muted sm">{{ $vehicleType->description }}</p>
                </article>
            @empty
                <p class="muted">No vehicle types are listed yet.</p>
            @endforelse
        </div>
        <div class="mt">{{ $vehicleTypes->links('info.pagination') }}</div>
    </div>
</section>
@endsection
