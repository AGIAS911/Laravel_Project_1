@extends('layouts.dashboard')
@section('page-title', 'Contact inbox')
@section('page-description', 'Messages received through the MoveIt contact form.')

@section('content')
    @forelse ($messages as $message)
        <article class="card card-body mb-3">
            <h2 class="h5">{{ $message->subject }}</h2>
            <p class="small text-body-secondary">{{ $message->name }} · {{ $message->email }} · {{ $message->created_at }}</p>
            <div class="product-message-body">{{ $message->message }}</div>
        </article>
    @empty
        <p class="card card-body">No messages yet.</p>
    @endforelse
    {{ $messages->links('pagination::bootstrap-5') }}
@endsection
