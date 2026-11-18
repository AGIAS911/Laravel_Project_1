@extends('layouts.dashboard')
@section('workspace-title' , 'Sevises')
@section('page-title','Services')
@section('actions')
    <a href="{{ route('services.create') }}">إضافة خدمة</a>
@endsection

@section('content')
 @forelse ($services as $service)
        <div>
            <strong>{{ $service->name }}</strong>
            <span>{{ $service->price }}</span>
            <a href="{{ route('services.show', $service) }}">عرض</a>
            <a href="{{ route('services.edit', $service) }}">تعديل</a>
        </div>
    @empty
        <p>لا توجد خدمات بعد.</p>
    @endforelse


    {{ $services->links() }}



@endsection

