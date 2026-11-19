@extends('layouts.dashboard')
@section('page-title', 'Notifications')

@section('actions')
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="btn btn-outline-secondary">Mark all as read</button>
    </form>
@endsection

@section('content')
    <div class="card">
        @forelse ($notifications as $notification)
            <article @class(['card-body border-bottom', 'workspace-unread' => !$notification->read_at])>
                <h2 class="h6 workspace-heading">{{ $notification->title }}</h2>
                <p class="mb-1">{{ $notification->message }}</p>
                <time class="small text-body-secondary" datetime="{{ $notification->created_at->toISOString() }}">{{ $notification->created_at->diffForHumans() }}</time>
                @if ($notification->url)
                    <a class="ms-2" href="{{ $notification->url }}">Open</a>
                @endif
                @unless ($notification->read_at)
                    <form class="d-inline" method="POST" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        <button class="btn btn-link btn-sm">Mark as read</button>
                    </form>
                @endunless
            </article>
        @empty
            <p class="card-body mb-0">No notifications yet.</p>
        @endforelse
    </div>
    <div class="mt-3">{{ $notifications->links('pagination::bootstrap-5') }}</div>
@endsection
