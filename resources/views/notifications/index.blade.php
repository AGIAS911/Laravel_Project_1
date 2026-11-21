@extends('layouts.dashboard')
@section('page-title', 'Notifications')
@section('page-icon', 'fa-bell')

@section('actions')
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="btn btn-outline-secondary"><i class="fa-solid fa-check-double me-1" aria-hidden="true"></i> Mark all as read</button>
    </form>
@endsection

@section('content')
    <div class="card workspace-notifications">
        @forelse ($notifications as $notification)
            <article @class(['card-body border-bottom', 'workspace-unread' => !$notification->read_at])>
                <div class="d-flex align-items-start gap-3">
                    <span class="workspace-notification-icon" aria-hidden="true"><i class="fa-solid fa-bell"></i></span>
                    <div class="flex-grow-1">
                        <h2 class="h6 workspace-heading">{{ $notification->title }} @unless ($notification->read_at)<span class="workspace-notification-dot" aria-label="Unread"></span>@endunless</h2>
                <p class="mb-1">{{ $notification->message }}</p>
                <time class="small text-body-secondary" datetime="{{ $notification->created_at->toISOString() }}">{{ $notification->created_at->diffForHumans() }}</time>
                @if ($notification->url)
                    <a class="ms-2" href="{{ route('notifications.open', $notification->id) }}">Open</a>
                @endif
                @unless ($notification->read_at)
                    <form class="d-inline" method="POST" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        <button class="btn btn-link btn-sm">Mark as read</button>
                    </form>
                @endunless
                    </div>
                </div>
            </article>
        @empty
            <p class="card-body mb-0">No notifications yet.</p>
        @endforelse
    </div>
    <div class="mt-3">{{ $notifications->links('pagination::bootstrap-5') }}</div>
@endsection
