@extends('layouts.app')

@section('body')
    <div class="container">
        <div class="moveit-workspace">
            <aside class="moveit-sidebar">
                <nav aria-label="{{ __('Dashboard navigation') }}">
                    <p class="moveit-sidebar-title">@yield('workspace-title', __('Dashboard'))</p>
                    <a class="moveit-sidebar-link" href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>
                        {{ __('Overview') }}
                    </a>
                    @yield('sidebar')
                </nav>
            </aside>

            <div class="moveit-workspace-content">
                @include('layouts.partials.flash')

                @if (View::hasSection('page-title') || View::hasSection('actions'))
                    <header class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        @hasSection('page-title')
                            <h1 class="h2 mb-0">@yield('page-title')</h1>
                        @endif
                        @yield('actions')
                    </header>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
@endsection
