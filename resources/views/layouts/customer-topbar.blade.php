<header class="customer-header">
    <a class="visually-hidden-focusable customer-skip" href="#customer-main">Skip to content</a>
    <nav class="navbar navbar-expand-lg" aria-label="Customer workspace">
        <div class="container customer-nav-inner">
            <a class="moveit-brand navbar-brand" href="{{ route('home') }}" aria-label="MoveIt dashboard">MOVE<span>IT</span></a>
            <button class="navbar-toggler customer-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#customer-menu"
                aria-controls="customer-menu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>

            <div class="collapse navbar-collapse" id="customer-menu">
                <ul class="navbar-nav customer-nav-links">
                    @foreach ([
                        ['home', 'home', 'Dashboard', 'fa-house'],
                        ['booking.index', 'booking.*', 'Bookings', 'fa-box'],
                        ['payments.index', 'payments.*', 'Payments', 'fa-receipt'],

                        ['profile.edit', 'profile.*', 'Profile', 'fa-user'],
                    ] as [$route, $pattern, $label, $icon])
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route($route) }}" @if (request()->routeIs($pattern) && !request()->routeIs('booking.create')) aria-current="page" @endif>
                                <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="customer-nav-actions">
                    @php($unread = auth()->user()->unreadNotifications()->count())
                    <a class="customer-notification-link" href="{{ route('notifications.index') }}"
                        aria-label="{{ $unread ? 'Notifications, '.$unread.' unread' : 'Notifications' }}"
                        @if (request()->routeIs('notifications.*')) aria-current="page" @endif>
                        <i class="fa-regular fa-bell" aria-hidden="true"></i>
                        @if ($unread)
                            <span class="customer-unread-count" aria-hidden="true">{{ $unread > 99 ? '99+' : $unread }}</span>
                        @endif
                    </a>
                    <a class="btn btn-primary btn-sm" href="{{ route('booking.create') }}" @if (request()->routeIs('booking.create')) aria-current="page" @endif>
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> New booking
                    </a>
                    <div class="dropdown customer-account-menu">
                        <button type="button" class="customer-account" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Customer account menu">
                            <span aria-hidden="true">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h2 class="dropdown-header">{{ auth()->user()->name }}</h2></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Account settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">Log out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
