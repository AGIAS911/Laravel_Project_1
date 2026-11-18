@extends('vehicle-types.layout')

@section('title', 'إضافة نوع مركبة')

@section('content')
    <section class="panel">
        <h1>إضافة نوع مركبة</h1>
        @include('vehicle-types._form', [
            'vehicleType' => null,
            'action' => route('vehicle-types.store'),
            'method' => 'POST',
        ])
    </section>
@endsection
