@extends('layouts.dashboard')

@section('title', 'Notifications | MoveIt')
@section('page-title', 'Notifications')
@section('page-description', 'Updates about your account and bookings.')

@section('actions')
    @if ($unreadCount)
        <form method="POST" action="{{ route('notifications.read-all') }}"
            data-popup-title="Mark all notifications as read?"
            data-popup-text="This clears the unread indicator for all notifications."
            data-popup-action="Mark all as read">
            @csrf
            <button class="btn btn-outline-secondary product-action" type="submit"><i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark all as read</button>
        </form>
    @endif
@endsection

@section('content')
    <nav class="customer-notifications-filters" aria-label="Filter notifications">
        <a href="{{ route('notifications.index') }}" @if (!$unreadOnly) aria-current="page" @endif>All notifications</a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" @if ($unreadOnly) aria-current="page" @endif>
            Unread <span class="customer-payment-count">{{ $unreadCount }}</span>
        </a>
    </nav>

    <div class="card customer-notifications" id="notification-list">
        @forelse ($notifications as $notification)
            @php
                [$icon, $action] = match ($notification->type) {
                    'booking' => ['fa-truck-moving', 'Open booking'],
                    'driver_status', \App\Notifications\DriverApprovedNotification::class => ['fa-id-card', 'View details'],
                    'payment' => ['fa-receipt', 'Open payment'],
                    default => ['fa-bell', 'View details'],
                };
                $created = $notification->created_at->timezone(config('booking.timezone'));
            @endphp
            <article @class(['customer-inbox-row', 'is-unread' => !$notification->read_at])>
                <span class="customer-inbox-icon" aria-hidden="true"><i class="fa-solid {{ $icon }}"></i></span>
                <div class="customer-inbox-copy">
                    <div class="customer-inbox-title">
                        <h2 class="h6 workspace-heading mb-0">{{ $notification->title ?? 'Notification' }}</h2>
                        @unless ($notification->read_at)<span class="customer-inbox-unread">Unread</span>@endunless
                    </div>
                    @if ($notification->message)<p class="mb-0">{{ $notification->message }}</p>@endif
                    <time class="small text-body-secondary" datetime="{{ $created->toIso8601String() }}"
                        title="{{ $created->format('d M Y, H:i') }} (Amman)">{{ $created->diffForHumans() }}</time>
                </div>
                <div class="customer-inbox-actions">
                    @if ($notification->url)
                        <a class="btn btn-outline-secondary product-action" href="{{ $notification->url }}">
                            {{ $action }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @endif
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            <button class="btn customer-inbox-mark product-action" type="submit">
                                <i class="fa-solid fa-check" aria-hidden="true"></i> Mark as read
                            </button>
                        </form>
                    @endunless
                </div>
            </article>
        @empty
            <div class="card-body customer-inbox-empty">
                <span class="customer-inbox-icon" aria-hidden="true"><i class="fa-regular fa-bell"></i></span>
                <h2 class="h6 mb-1">{{ $unreadOnly ? 'You’re all caught up.' : 'No notifications yet.' }}</h2>
                <p class="text-body-secondary mb-0">{{ $unreadOnly ? 'New unread notifications will appear here.' : 'Booking and account updates will appear here.' }}</p>
                @if ($unreadOnly)
                    <a class="d-inline-block mt-2" href="{{ route('notifications.index') }}">See all notifications</a>
                @endif
            </div>
        @endforelse
    </div>
    <div class="mt-3">{{ $notifications->links('pagination::bootstrap-5') }}</div>
@endsection
