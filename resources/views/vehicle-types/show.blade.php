@extends('vehicle-types.layout')

@section('title', $vehicleType->name)

@section('content')
    <section class="panel">
        <h1>{{ $vehicleType->name }}</h1>
        <p><strong>الحالة:</strong> {{ $vehicleType->is_active ? 'نشط' : 'غير نشط' }}</p>
        <p><strong>الوصف:</strong> {{ $vehicleType->description ?: 'لا يوجد وصف.' }}</p>
        <p><strong>عدد المركبات المرتبطة:</strong> {{ $vehicleType->vehicles_count }}</p>
        @if ($vehicleType->image)
            <p><img src="{{ asset($vehicleType->image) }}" alt="صورة {{ $vehicleType->name }}" style="max-width: 320px; max-height: 240px"></p>
        @endif
        <div class="actions">
            <a class="button" href="{{ route('vehicle-types.edit', $vehicleType) }}">تعديل</a>
            <a class="button secondary" href="{{ route('vehicle-types.index') }}">رجوع للقائمة</a>
        </div>
    </section>
@endsection
