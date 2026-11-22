@extends('layouts.app')

@section('body')
    <div class="container">
        <div class="row g-4">
            @hasSection('sidebar')
                <aside class="col-md-3">
                    <h2 class="h6">@yield('workspace-title', __('Dashboard'))</h2>
                    @yield('sidebar')
                </aside>
            @endif

            <section class="col product-page">
                <header class="product-page-header" @hasSection('page-anchor') id="@yield('page-anchor')" @endif>
                    <div>
                        <h1 class="workspace-heading">@yield('page-title', __('Dashboard'))</h1>
                        @hasSection('page-description')
                            <p class="text-body-secondary mb-0 mt-1">@yield('page-description')</p>
                        @endif
                    </div>
                    @hasSection('actions')
                        <div class="product-page-actions">@yield('actions')</div>
                    @endif
                </header>

                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif
                @yield('content')
            </section>
        </div>
    </div>
@endsection
