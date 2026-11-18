@extends('layouts.dashboard')
@section('workspace-title' , 'Sevises')
@section('page-title','Services')

@section('content')

@include('services._form', ['service' => null])


@endsection

