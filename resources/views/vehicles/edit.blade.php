@extends('vehicle-types.layout')

@section('title', 'تعديل المركبة')

@section('content')
    <section class="panel">
        <h1>تعديل المركبة</h1>
        @include('vehicles._form', [
            'vehicle' => $vehicle,
            'action' => route('vehicles.update', $vehicle),
            'method' => 'PUT',
        ])
    </section>
@endsection
