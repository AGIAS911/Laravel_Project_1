@extends('vehicle-types.layout')

@section('title', 'Add Vehicle')

@section('content')
    <header class="product-page-header">
        <div><h1 class="product-title">Add Vehicle</h1><p class="text-body-secondary mt-1 mb-0">Add a driver vehicle to the fleet.</p></div>
        <a class="btn btn-outline-secondary" href="{{ route('vehicles.index') }}">Back to vehicles</a>
    </header>
    <section class="panel product-editor">
        @include('vehicles._form', [
            'vehicle' => null,
            'action' => route('vehicles.store'),
            'method' => 'POST',
        ])
    </section>
@endsection
