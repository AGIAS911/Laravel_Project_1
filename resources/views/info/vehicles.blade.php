@extends('layouts.info')

@section('title', 'Vehicle types')

@section('content')
<div class="container">
    <section class="page-hero">
        <div class="crumbs"><a href="{{ url('/') }}">Home</a> / Vehicle types</div>
        <h1>Vehicle types</h1>
        <p class="lead mt-s">Choose a vehicle type that fits your move. MoveIt assigns an available vehicle and driver for your appointment.</p>
    </section>
</div>
<section class="section">
    <div class="container">
        <div class="grid g3 product-vehicle-catalog">
            @forelse ($vehicleTypes as $vehicleType)
                <article class="v-card">
                    <div class="veh">
                        <x-vehicle-illustration :name="$vehicleType->name" :photo="$vehicleType->image" loading="lazy" />
                    </div>
                    <h3>{{ $vehicleType->name }}</h3>
                    <p class="muted sm">{{ $vehicleType->description }}</p>
                </article>
            @empty
                <div class="panel">
                    <p>No vehicle types are listed yet.</p>
                    <a class="link" href="{{ route('info.contact') }}">Ask about available vehicles</a>
                </div>
            @endforelse
        </div>
        <div class="mt">{{ $vehicleTypes->links('info.pagination') }}</div>
    </div>
</section>
@endsection
