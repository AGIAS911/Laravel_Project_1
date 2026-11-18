@extends('vehicle-types.layout')

@section('title', 'Edit Vehicle Type')

@section('content')
    <section class="panel">
        <h1>Edit Vehicle Type</h1>
        @include('vehicle-types._form', [
            'vehicleType' => $vehicleType,
            'action' => route('vehicle-types.update', $vehicleType),
            'method' => 'PUT',
        ])
    </section>
@endsection
