@extends('vehicle-types.layout')

@section('title', 'إضافة مركبة')

@section('content')
    <section class="panel">
        <h1>إضافة مركبة</h1>
        @include('vehicles._form', [
            'vehicle' => null,
            'action' => route('vehicles.store'),
            'method' => 'POST',
        ])
    </section>
@endsection
