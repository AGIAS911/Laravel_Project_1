@extends('layouts.dashboard')

@section('title', 'Dashboard | MoveIt')
@section('page-title', 'Dashboard')

@section('content')
    <section class="product-task">
        <p class="mb-0">You're logged in. Open your workspace to manage your activity.</p>
        <a class="btn btn-primary" href="{{ route('home') }}">Open your workspace</a>
    </section>
@endsection
