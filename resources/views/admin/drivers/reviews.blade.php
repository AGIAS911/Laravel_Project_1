@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="h3 mb-1">Ratings for {{ $driverProfile->user->name }}</h1>
                <p class="text-muted mb-0">Customer ratings and report comments for this driver.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('admin.driver-reviews.index') }}">
                All drivers
            </a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Customer</th>
                            <th scope="col">Rating</th>
                            <th scope="col">Comment / report</th>
                            <th scope="col">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reviews as $review)
                            <tr>
                                <td>{{ $review->booking->customer->name }}</td>
                                <td>{{ $review->rating }} / 5</td>
                                <td>{{ $review->comment ?: '—' }}</td>
                                <td>{{ $review->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No ratings for this driver yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $reviews->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
