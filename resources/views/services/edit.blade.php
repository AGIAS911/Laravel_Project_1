@extends('layouts.dashboard')
@section('workspace-title' , 'Sevises')
@section('page-title','Services')

@section('content')

<h1>Edit Service</h1>

@include('services._form', ['service' => $service])

@endsection

