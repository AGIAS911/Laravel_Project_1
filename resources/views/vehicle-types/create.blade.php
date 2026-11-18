@extends('vehicle-types.layout')

@section('title', 'Add Vehicle Type')

@section('content')
    <section class="panel">
        <h1>Add Vehicle Type</h1>
        @include('vehicle-types._form', [
            'vehicleType' => null,
            'action' => route('vehicle-types.store'),
            'method' => 'POST',
        ])
    </section>
@endsection
