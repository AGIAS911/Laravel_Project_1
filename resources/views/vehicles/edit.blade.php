@extends('vehicle-types.layout')

@section('title', 'Edit Vehicle')

@section('content')
    <section class="panel">
        <h1>Edit Vehicle</h1>
        @include('vehicles._form', [
            'vehicle' => $vehicle,
            'action' => route('vehicles.update', $vehicle),
            'method' => 'PUT',
        ])
    </section>
@endsection
