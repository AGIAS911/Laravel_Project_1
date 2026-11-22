@extends('vehicle-types.layout')

@section('title', $vehicleType->name)

@section('content')
    <section class="panel">
        <header class="product-page-header">
            <h1 class="product-title">{{ $vehicleType->name }}</h1>
            @include('booking._status', ['state' => $vehicleType->is_active ? 'active' : 'inactive'])
        </header>
        <p><strong>Status:</strong> {{ $vehicleType->is_active ? 'Active' : 'Inactive' }}</p>
        <p><strong>Description:</strong> {{ $vehicleType->description ?: 'No description.' }}</p>
        <p><strong>Linked vehicles:</strong> {{ $vehicleType->vehicles_count }}</p>
        <p><x-vehicle-illustration :name="$vehicleType->name" :photo="$vehicleType->image" class="product-vehicle-preview" /></p>
        <div class="actions">
            @if (auth()->user()?->role === 'admin')
                <a class="btn btn-primary product-action" href="{{ route('vehicle-types.edit', $vehicleType) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit</a>
            @endif
            <a class="btn btn-outline-secondary product-action" href="{{ route('vehicle-types.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to vehicle types</a>
        </div>
    </section>
@endsection
