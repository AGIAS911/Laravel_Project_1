@extends('layouts.dashboard')

@section('title', 'Driver dashboard')
@section('page-title', 'Welcome, ' . auth()->user()->name)

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('driver.profile.show') }}">Driver profile</a>
@endsection

@section('content')
    @if ($driver->status === 'approved')
        <div class="row g-3 mb-4">
            @foreach (['pending' => 'New requests', 'confirmed' => 'Confirmed', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $status => $label)
                <div class="col-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <strong class="fs-3 d-block workspace-heading">{{ $counts[$status] ?? 0 }}</strong>
                            <span>{{ $label }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="workspace-banner d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <div>
                <h2 class="h4">Manage your moves</h2>
                <p class="mb-0">Review incoming requests and check your upcoming schedule.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @php($pendingRequests = $counts['pending'] ?? 0)
                <a class="btn btn-primary" href="{{ route('driver.bookings.index', ['view' => 'requests']) }}"
                    aria-label="New requests: {{ $pendingRequests }}">
                    New requests
                    @if ($pendingRequests > 0)
                        <span class="badge text-bg-light text-primary ms-1" aria-hidden="true">{{ $pendingRequests }}</span>
                    @endif
                </a>
                <a class="btn btn-outline-light" href="{{ route('driver.schedule') }}">Schedule</a>
            </div>
        </section>
    @else
        <p class="alert alert-info">Your driver profile is under review. You can check your status in your profile.</p>
    @endif
@endsection
