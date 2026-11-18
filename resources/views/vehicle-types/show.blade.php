@extends('vehicle-types.layout')

@section('title', $vehicleType->name)

@section('content')
    <section class="panel">
        <h1>{{ $vehicleType->name }}</h1>
        <p><strong>Status:</strong> {{ $vehicleType->is_active ? 'Active' : 'Inactive' }}</p>
        <p><strong>Description:</strong> {{ $vehicleType->description ?: 'No description.' }}</p>
        <p><strong>Linked vehicles:</strong> {{ $vehicleType->vehicles_count }}</p>
        @if ($vehicleType->image)
            <p><img src="{{ asset($vehicleType->image) }}" alt="Image of {{ $vehicleType->name }}" style="max-width: 320px; max-height: 240px"></p>
        @endif
        <div class="actions">
            <a class="button" href="{{ route('vehicle-types.edit', $vehicleType) }}">Edit</a>
            <a class="button secondary" href="{{ route('vehicle-types.index') }}">Back to Vehicle Types</a>
        </div>
    </section>
@endsection
