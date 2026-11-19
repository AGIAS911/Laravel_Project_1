@extends('layouts.app')

@section('body')
    <div class="container py-4">
        <div class="row">
            <aside class="col-md-3">
                <h2>@yield('workspace-title', __('Dashboard'))</h2>
                <a href="{{ route('home') }}">{{ __('Overview') }}</a>
                @yield('sidebar')
            </aside>

            <section class="col-md-9">
                <header class="d-flex justify-content-between align-items-center mb-4">
                    <h1>@yield('page-title', __('Dashboard'))</h1>
                    @yield('actions')
                </header>

                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif

                @yield('content')
            </section>
        </div>
    </div>
@endsection
