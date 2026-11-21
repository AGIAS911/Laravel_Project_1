@php
    $driverAccount = auth()->user();
    $approvedDriver = $driverAccount->driverProfile?->status === 'approved';
    $unreadCount = $driverAccount->unreadNotifications()->count();
    $notificationPreview = $driverAccount->unreadNotifications()->latest()->limit(4)->get();
    $driverLinks = [
        ['driver.dashboard', 'driver.dashboard', 'Dashboard', 'fa-gauge-high'],
        ...($approvedDriver ? [
            ['driver.bookings.index', 'driver.bookings.*', 'Bookings', 'fa-clipboard-list'],
            ['driver.schedule', 'driver.schedule', 'Schedule', 'fa-calendar-days'],
            ['vehicles.index', 'vehicles.*', 'Vehicles', 'fa-truck'],
        ] : []),
    ];
@endphp

<header class="driver-header">
    <a class="visually-hidden-focusable driver-skip" href="#driver-main">Skip to content</a>
    <div class="driver-header-inner">
        <a class="moveit-brand driver-brand" href="{{ route('driver.dashboard') }}" aria-label="MoveIt driver dashboard">MOVE<span>IT</span></a>

        <nav class="driver-nav" aria-label="Driver workspace">
            @foreach ($driverLinks as [$routeName, $pattern, $label, $icon])
                <a href="{{ route($routeName) }}" @if (request()->routeIs($pattern) || ($routeName === 'driver.bookings.index' && request()->routeIs('booking.show'))) aria-current="page" @endif>
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="driver-header-actions">
            <div class="dropdown">
                <button class="driver-notification" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    aria-label="{{ $unreadCount ? 'Notifications, '.$unreadCount.' unread' : 'Notifications' }}">
                    <i class="fa-regular fa-bell" aria-hidden="true"></i>
                    @if ($unreadCount)<span aria-hidden="true">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>@endif
                </button>
                <div class="dropdown-menu dropdown-menu-end driver-notification-menu">
                    <ul class="list-unstyled mb-0">
                        @forelse ($notificationPreview as $notification)
                            <li class="driver-notification-item">
                                @if ($notification->url)
                                    <a href="{{ $notification->url }}">{{ $notification->title }}</a>
                                @else
                                    <strong>{{ $notification->title }}</strong>
                                @endif
                                <p>{{ $notification->message }}</p>
                            </li>
                        @empty
                            <li class="driver-notification-item">No unread notifications.</li>
                        @endforelse
                    </ul>
                    <a class="dropdown-item" href="{{ route('notifications.index') }}">View all notifications</a>
                </div>
            </div>

            <div class="dropdown">
                <button class="driver-account" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Driver account menu">
                    <span aria-hidden="true">{{ str($driverAccount->name)->substr(0, 1)->upper() }}</span>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end driver-account-menu">
                    <li><span class="dropdown-header text-wrap">{{ $driverAccount->name }}</span></li>
                    <li><a class="dropdown-item" href="{{ route('driver.profile.show') }}"><i class="fa-regular fa-id-card" aria-hidden="true"></i> Driver profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-gear" aria-hidden="true"></i> Account settings</a></li>
                    <li><a class="dropdown-item" href="{{ route('services.index') }}"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i> Services</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item" type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Log out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
