@extends('layouts.dashboard')
@section('workspace-title' , 'Sevises')
@section('page-title','Services')

@section('content')

<h1>Service Details</h1>

<div>
    <p><strong>Name:</strong> {{ $service->name }}</p>
    <p><strong>Type:</strong> {{ $service->type }}</p>
    <p><strong>Unit:</strong> {{ $service->unit }}</p>
</div>

<a href="{{ route('services.edit', $service->id) }}">
    Edit Service
</a>

<a href="{{ route('services.index') }}">
    Back to Services
</a>

<form action="{{ route('services.destroy', $service->id) }}" method="POST">
    @csrf
    @method('DELETE')

    <button type="submit">Delete Service</button>
</form>


@endsection

