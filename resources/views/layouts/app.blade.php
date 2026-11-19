<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a @class(['nav-link', 'active' => request()->routeIs('home')])
                                    href="{{ route('home') }}"
                                    @if (request()->routeIs('home')) aria-current="page" @endif>{{ __('Overview') }}</a>
                            </li>
                            <li class="nav-item">
                                <a @class(['nav-link', 'active' => request()->routeIs('booking.*')])
                                    href="{{ route('booking.index') }}"
                                    @if (request()->routeIs('booking.*')) aria-current="page" @endif>{{ __('Bookings') }}</a>
                            </li>
                            @if (Auth::user()->role === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.users.index') }}">إدارة المستخدمين</a>
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
                                            <strong>{{ $notification->data['title'] ?? 'إشعار جديد' }}</strong>
                                            <p class="mb-1">{{ $notification->data['message'] ?? '' }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                @if (!empty($notification->data['url']))
                                                    <a href="{{ $notification->data['url'] }}">فتح</a>
                                                @endif
                                                <form method="POST"
                                                      action="{{ route('notifications.read', $notification->id) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-link" type="submit">
                                                        Mark as Readed !
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="dropdown-item-text text-muted">No Notification Yet .</span>
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

        <main class="py-4">
            @hasSection('body')
                @yield('body')
            @else
                @yield('content')
            @endif
        </main>
    </div>
</body>
</html>
