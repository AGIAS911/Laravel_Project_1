@extends('layouts.dashboard')

@section('workspace-title', 'Services')
@section('page-title', 'Create service')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('services.index') }}">Back to services</a>
@endsection

@section('content')
    @include('services._form', ['service' => null])
@endsection