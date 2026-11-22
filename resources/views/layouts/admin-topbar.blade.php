        <header class="admin-topbar">
            <button type="button" class="admin-menu-toggle" aria-label="Workspace menu" aria-controls="admin-sidebar" aria-expanded="false">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <form class="admin-search" method="GET" action="{{ route('booking.index') }}" role="search">
                <button type="submit" aria-label="Search bookings"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                <input type="search" name="q" aria-label="Search by booking ID" placeholder="Search by booking ID">
            </form>
            @php($unread = auth()->user()->unreadNotifications()->count())
            <a class="admin-bell" href="{{ route('notifications.index') }}" aria-label="{{ $unread ? 'Notifications, '.$unread.' unread' : 'Notifications' }}"
                @if (request()->routeIs('notifications.*')) aria-current="page" @endif>
                <i class="fa-regular fa-bell" aria-hidden="true"></i>
                @if ($unread)<span class="admin-count" aria-hidden="true">{{ $unread > 99 ? '99+' : $unread }}</span>@endif
            </a>
            <details class="admin-account">
                <summary aria-label="Administrator account menu"><span class="admin-avatar" aria-hidden="true">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role === 'customer' ? 'Customer account' : 'Administrator' }}</small></span></summary>
                <div>
                    <a href="{{ route('profile.edit') }}">Account settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Log out</button>
                    </form>
                </div>
            </details>
        </header>
