@php
    $adminPendingDrivers = $adminPendingDrivers ?? \App\Models\DriverProfile::where('status', 'pending')->count();
    $adminPendingReceipts = $adminPendingReceipts ?? \App\Models\Payment::where('status', \App\Models\Payment::STATUS_PENDING)->count();
@endphp
<aside class="admin-sidebar" id="admin-sidebar">
    <a class="moveit-brand" href="{{ route('admin.dashboard') }}" aria-label="MoveIt admin dashboard">MOVE<span>IT</span></a>
    <p class="admin-role"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Admin</p>
    <nav aria-label="Admin workspace">
        <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
            <i class="fa-solid fa-gauge" aria-hidden="true"></i> Home
        </a>
        @foreach ([
            'Users' => [
                ['admin.users.index', 'All users', 'fa-users'],
                ['admin.users.index', 'Customers', 'fa-user', null, '?role=customer'],
                ['admin.users.index', 'Drivers', 'fa-id-card', null, '?role=driver'],
                ['admin.driver-approvals', 'Driver approvals', 'fa-user-check', $adminPendingDrivers],
            ],
            'Fleet and services' => [
                ['vehicle-types.index', 'Vehicle types', 'fa-tags'],
                ['vehicles.index', 'Vehicles', 'fa-truck'],
                ['services.index', 'Services', 'fa-screwdriver-wrench'],
                ['admin.pricing-rules.index', 'Time rules', 'fa-clock'],
            ],
            'Operations and finance' => [
                ['booking.index', 'All bookings', 'fa-clipboard-list'],
                ['admin.payments.index', 'CliQ review', 'fa-money-check-dollar', $adminPendingReceipts],
                ['admin.driver-reviews.index', 'Reviews', 'fa-star'],
                ['admin.reports', 'Commission reports', 'fa-chart-column'],
            ],
            'Settings and support' => [
                ['admin.settings', 'Settings and commission', 'fa-sliders'],
                ['notifications.index', 'Notifications', 'fa-bell'],
                ['admin.inbox', 'Contact inbox', 'fa-inbox'],
            ],
        ] as $group => $links)
            <p class="admin-nav-group">{{ $group }}</p>
            @foreach ($links as $link)
                @php
                    $querySuffix = $link[4] ?? '';
                    if ($querySuffix !== '' && str_starts_with($querySuffix, '?')) {
                        parse_str(ltrim($querySuffix, '?'), $queryParams);
                        $isActive = request()->routeIs($link[0])
                            && collect($queryParams)->every(fn ($val, $key) => request()->query($key) === (string) $val);
                    } else {
                        $isActive = empty($querySuffix)
                            && ($link[0] !== 'admin.users.index' || !request()->has('role'))
                            && request()->routeIs(str_ends_with($link[0], '.index') ? substr($link[0], 0, -6) . '.*' : $link[0]);
                    }
                @endphp
                <a href="{{ route($link[0]) }}{{ $querySuffix }}" @if ($isActive) aria-current="page" @endif>
                    <i class="fa-solid {{ $link[2] }}" aria-hidden="true"></i> {{ $link[1] }}
                    @if (!empty($link[3]))<span class="admin-count">{{ $link[3] }}</span>@endif
                </a>
            @endforeach
        @endforeach
    </nav>
    <form class="admin-signout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Log out</button>
    </form>
</aside>
