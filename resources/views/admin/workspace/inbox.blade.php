@extends('layouts.dashboard')
@section('page-title', 'Contact inbox')

@section('content')
    @forelse ($messages as $message)
        <article class="card card-body mb-3">
            <h2 class="h5">{{ $message->subject }}</h2>
            <p class="small text-body-secondary">{{ $message->name }} · {{ $message->email }} · {{ $message->created_at }}</p>
            <div class="text-break" style="white-space:pre-wrap">{{ $message->message }}</div>
        </article>
    @empty
        <p>No messages yet.</p>
    @endforelse
    {{ $messages->links('pagination::bootstrap-5') }}
@endsection
