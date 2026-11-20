@extends('layouts.dashboard')
@section('workspace-title', 'Services')
@section('page-title', $service->name)

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('services.index') }}">Back to services</a>
    @if (auth()->user()?->role === 'admin')
        <a class="btn btn-primary" href="{{ route('services.edit', $service) }}">Edit service</a>
    @endif
@endsection

@section('content')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Service details</h2>
            <span class="badge {{ $service->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                {{ $service->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <dl class="row card-body mb-0">
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $service->name }}</dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $service->description ?: '—' }}</dd>

            <dt class="col-sm-3">Type</dt>
            <dd class="col-sm-9">{{ $service->type }}</dd>

            <dt class="col-sm-3">Unit</dt>
            <dd class="col-sm-9">{{ $service->unit }}</dd>

            <dt class="col-sm-3">Price</dt>
            <dd class="col-sm-9">{{ number_format((float) $service->price, 3) }} JOD</dd>
        </dl>

        @if (auth()->user()?->role === 'admin')
            <div class="card-footer text-end">
                <form action="{{ route('services.destroy', $service) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit"
                            onclick="return confirm('Delete this service?')">Delete service</button>
                </form>
            </div>
        @endif
    </div>
@endsection
