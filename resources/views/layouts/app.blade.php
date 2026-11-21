<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link rel="icon" href="{{ asset('landing/img/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('landing/css/brand.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/css/workspace.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chatbot-widget.css') }}">
    @if (auth()->user()?->role === 'admin')
        <link rel="stylesheet" href="{{ asset('landing/css/admin-dashboard.css') }}">
        <script src="{{ asset('landing/js/admin-navigation.js') }}" defer></script>
    @elseif (auth()->user()?->role === 'customer')
        <link rel="stylesheet" href="{{ asset('landing/css/customer-workspace.css') }}">
    @endif
    @stack('styles')
</head>
<body class="moveit-workspace">
    <div id="app">
        @if (auth()->user()?->role === 'admin')
            <div class="admin-shell">
                @include('layouts.admin-sidebar')
                <div class="admin-main">
                    @include('layouts.admin-topbar')
                    <main class="workspace-content admin-workspace-content py-4">
                        @include('layouts.workspace-content')
                    </main>
                </div>
            </div>
        @elseif (auth()->user()?->role === 'customer')
            <div class="customer-shell">
                @include('layouts.customer-topbar')
                <main class="customer-workspace-content" id="customer-main">
                    @include('layouts.workspace-content')
                </main>
            </div>
        @else
        @if (auth()->user()?->role !== 'driver')
        <nav class="navbar navbar-expand-md moveit-topbar">
            <div class="container">
                <a class="navbar-brand moveit-brand" aria-label="MoveIt" href="{{ url('/') }}">
                    MOVE<span>IT</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        @auth
                            @php
                                $dashboardRoute = match (Auth::user()->role) {
                                    'admin' => 'admin.dashboard',
                                    'driver' => 'driver.dashboard',
                                    default => 'home',
                                };
                                $bookingsRoute = Auth::user()->role === 'driver' ? 'driver.bookings.index' : 'booking.index';
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route($dashboardRoute) }}" @if (request()->routeIs($dashboardRoute)) aria-current="page" @endif>Dashboard</a>
                            </li>
                            @if (Auth::user()->role !== 'driver' || Auth::user()->driverProfile?->status === 'approved')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route($bookingsRoute) }}" @if (request()->routeIs($bookingsRoute)) aria-current="page" @endif>Bookings</a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            @php($unreadNotifications = Auth::user()->unreadNotifications)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown"
                                   role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Notifications
                                    @if ($unreadNotifications->isNotEmpty())
                                        <span class="badge text-bg-danger">{{ $unreadNotifications->count() }}</span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notificationsDropdown"
                                     style="min-width: 20rem; max-height: 24rem; overflow-y: auto;">
                                    @forelse ($unreadNotifications as $notification)
                                        <div class="border-bottom py-2">
                                            <strong>{{ $notification->data['title'] ?? 'New notification' }}</strong>
                                            <p class="mb-1">{{ $notification->data['message'] ?? '' }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                @if (!empty($notification->data['url']))
                                                    <a href="{{ route('notifications.open', $notification->id) }}">Open</a>
                                                @endif
                                                <form method="POST"
                                                      action="{{ route('notifications.read', $notification->id) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-link" type="submit">
                                                        Mark as read
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="dropdown-item-text text-muted">No unread notifications.</span>
                                    @endforelse
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @endif

        <div @class(['workspace-shell' => auth()->check()])>
            @auth
                @include('layouts.workspace-menu')
            @endauth
        <main class="workspace-content py-4">
            @include('layouts.workspace-content')
        </main>
        </div>
        @endif
    </div>
    @include('chatbot.widget')
</body>
</html>
