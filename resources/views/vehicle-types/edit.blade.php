@extends('vehicle-types.layout')

@section('title', 'تعديل نوع المركبة')

@section('content')
    <section class="panel">
        <h1>تعديل نوع المركبة</h1>
        @include('vehicle-types._form', [
            'vehicleType' => $vehicleType,
            'action' => route('vehicle-types.update', $vehicleType),
            'method' => 'PUT',
        ])
    </section>
@endsection
