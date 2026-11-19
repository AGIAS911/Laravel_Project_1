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

            <section class="col">
                <header class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h1 class="h3 workspace-heading mb-0">
                            @if (auth()->user()?->role === 'customer' && View::hasSection('page-icon'))
                                <span class="customer-page-icon" aria-hidden="true"><i class="fa-solid @yield('page-icon')"></i></span>
                            @endif
                            @yield('page-title', __('Dashboard'))
                        </h1>
                        @hasSection('page-description')
                            <p class="text-body-secondary mb-0 mt-1">@yield('page-description')</p>
                        @endif
                    </div>
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
