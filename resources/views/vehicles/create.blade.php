@extends('vehicle-types.layout')

@section('title', 'Add Vehicle')

@section('content')
    <section class="panel">
        <h1>Add Vehicle</h1>
        @include('vehicles._form', [
            'vehicle' => null,
            'action' => route('vehicles.store'),
            'method' => 'POST',
        ])
    </section>
@endsection
