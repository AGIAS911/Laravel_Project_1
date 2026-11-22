@extends('vehicle-types.layout')

@section('title', 'Add Vehicle Type')

@section('content')
    <header class="product-page-header">
        <div><h1 class="product-title">Add Vehicle Type</h1><p class="text-body-secondary mt-1 mb-0">Add a size customers can choose for their move.</p></div>
        <a class="btn btn-outline-secondary product-action" href="{{ route('vehicle-types.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to vehicle types</a>
    </header>
    <section class="panel product-editor">
        @include('vehicle-types._form', [
            'vehicleType' => null,
            'action' => route('vehicle-types.store'),
            'method' => 'POST',
        ])
    </section>
@endsection
