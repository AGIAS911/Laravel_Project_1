<aside class="admin-sidebar customer-sidebar" id="admin-sidebar">
    <a class="moveit-brand" href="{{ url('/') }}" aria-label="MoveIt home">MOVE<span>IT</span></a>
    <p class="admin-role"><i class="fa-solid fa-user me-1" aria-hidden="true"></i> Customer</p>
    <nav aria-label="Customer workspace">
        @foreach ([
            ['home', 'home', 'Dashboard', 'fa-gauge'],
            ['booking.create', 'booking.create', 'New booking', 'fa-circle-plus'],
            ['booking.index', 'booking.*', 'My bookings', 'fa-box'],
            ['payments.index', 'payments.*', 'Payments', 'fa-receipt'],
            ['notifications.index', 'notifications.*', 'Notifications', 'fa-bell'],
            ['profile.edit', 'profile.*', 'Profile', 'fa-id-card'],
        ] as [$route, $pattern, $label, $icon])
            @php($active = request()->routeIs($pattern) && ($route !== 'booking.index' || !request()->routeIs('booking.create')))
            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif>
                <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }}
            </a>
        @endforeach
    </nav>
    <form class="admin-signout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Log out</button>
    </form>
</aside>
