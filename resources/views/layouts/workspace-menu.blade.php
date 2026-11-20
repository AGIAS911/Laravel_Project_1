@php
$role = auth()->user()->role;
$links = match ($role) {
'admin' => [
'admin.dashboard' => 'Dashboard',
'booking.index' => 'Bookings',
'admin.payments.index' => 'Payments',
'admin.users.index' => 'Users and drivers',
'admin.driver-reviews.index' => 'Driver reviews',
'vehicles.index' => 'Vehicles',
'vehicle-types.index' => 'Vehicle types',
'services.index' => 'Services',
'admin.pricing-rules.index' => 'Pricing rules',
'admin.reports' => 'Reports',
'admin.settings' => 'Settings',
'admin.inbox' => 'Contact inbox',
],
'driver' => [
'driver.dashboard' => 'Dashboard',
...((auth()->user()->driverProfile?->status === 'approved') ? ['driver.bookings.index' => 'Bookings', 'driver.schedule' => 'Schedule', 'vehicles.index' => 'Vehicles'] : []),
'services.index' => 'Services',
'driver.profile.show' => 'Driver profile',
],
default => [
'home' => 'Dashboard',
'booking.create' => 'New booking',
'booking.index' => 'My bookings',
'services.index' => 'Services',
'payments.index' => 'Payments',
],
};
@endphp

<nav class="workspace-menu" aria-label="{{ __('Workspace') }}">
    <p class="workspace-role">{{ ucfirst($role) }}</p>
    @foreach ($links as $name => $label)
    @php($icon = match ($label) {
    'Dashboard' => 'fa-gauge',
    'New booking' => 'fa-circle-plus',
    'Bookings', 'My bookings' => 'fa-box',
    'Payments' => 'fa-receipt',
    'Vehicles', 'Vehicle types' => 'fa-truck',
    'Services' => 'fa-boxes-stacked',
    'Users and drivers' => 'fa-users',
    'Driver reviews' => 'fa-star',
    default => 'fa-id-card',
    })
    <a href="{{ route($name) }}"
        @if (request()->routeIs($name)
        || ($name === 'services.index' && request()->routeIs('services.*'))
        || ($name === 'driver.profile.show' && request()->routeIs('driver.profile.*'))) aria-current="page" @endif>
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
        {{ __($label) }}
    </a>
    @endforeach
    @if ($role === 'driver' && auth()->user()->driverProfile?->status === 'approved')
    <a href="{{ route('driver.bookings.index', ['view' => 'requests']) }}"><i class="fa-solid fa-inbox" aria-hidden="true"></i> New requests</a>
    @endif
    <a href="{{ route('notifications.index') }}" @if (request()->routeIs('notifications.index')) aria-current="page" @endif>
        <i class="fa-solid fa-bell" aria-hidden="true"></i> Notifications
    </a>
    <a href="{{ route('profile.edit') }}" @if (request()->routeIs('profile.edit')) aria-current="page" @endif>
        <i class="fa-solid fa-id-card" aria-hidden="true"></i>
        {{ __('Account settings') }}
    </a>
</nav>