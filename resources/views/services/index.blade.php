@extends('layouts.dashboard')

@section('workspace-title', 'Services')
@section('page-title', 'Services')

@section('actions')
    <a class="btn btn-primary" href="{{ route('services.create') }}">إضافة خدمة</a>
@endsection

@section('content')
    <div class="card shadow-sm">
        @forelse ($services as $service)
            <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3">
                <div>
                    <h2 class="h5 mb-1">{{ $service->name }}</h2>
                    <span class="badge text-bg-light border">{{ number_format((float) $service->price, 2) }}</span>
                </div>

                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('services.show', $service) }}">عرض</a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('services.edit', $service) }}">تعديل</a>
                </div>
            </div>
        @empty
            <div class="card-body text-center text-muted py-5">
                <p class="mb-0">لا توجد خدمات بعد.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $services->links('pagination::bootstrap-5') }}
    </div>
@endsection

