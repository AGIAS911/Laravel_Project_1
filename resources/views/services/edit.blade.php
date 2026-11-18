@extends('layouts.dashboard')

@section('workspace-title', 'Services')
@section('page-title', 'Edit service')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('services.show', $service) }}">Back to service</a>
@endsection

@section('content')
    @include('services._form', ['service' => $service])
@endsection
