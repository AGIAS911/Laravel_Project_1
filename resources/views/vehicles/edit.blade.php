@extends('vehicle-types.layout')

@section('title', 'Edit Vehicle')

@section('content')
    <header class="product-page-header">
        <div><h1 class="product-title">Edit Vehicle</h1><p class="text-body-secondary mt-1 mb-0">Update details, capacity and pricing for this vehicle.</p></div>
        <a class="btn btn-outline-secondary" href="{{ route('vehicles.index') }}">Back to vehicles</a>
    </header>
    <section class="panel product-editor">
        @include('vehicles._form', [
            'vehicle' => $vehicle,
            'action' => route('vehicles.update', $vehicle),
            'method' => 'PUT',
        ])
    </section>
@endsection
