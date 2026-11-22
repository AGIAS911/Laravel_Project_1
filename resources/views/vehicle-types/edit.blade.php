@extends('vehicle-types.layout')

@section('title', 'Edit Vehicle Type')

@section('content')
    <header class="product-page-header">
        <div><h1 class="product-title">Edit Vehicle Type</h1><p class="text-body-secondary mt-1 mb-0">Update the catalog name, description, or photo.</p></div>
        <a class="btn btn-outline-secondary product-action" href="{{ route('vehicle-types.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to vehicle types</a>
    </header>
    <section class="panel product-editor">
        @include('vehicle-types._form', [
            'vehicleType' => $vehicleType,
            'action' => route('vehicle-types.update', $vehicleType),
            'method' => 'PUT',
        ])
    </section>
@endsection
