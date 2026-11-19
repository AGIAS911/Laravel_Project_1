@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h1 class="h3 workspace-heading mb-1">Reviews</h1>
        <p class="text-body-secondary mb-4">One review per completed booking.</p>
        <div class="table-responsive mb-3">
            <table class="table align-middle mb-0">
                <thead><tr><th scope="col">Booking</th><th scope="col">Customer</th><th scope="col">Driver</th><th scope="col">Rating</th><th scope="col">Comment</th></tr></thead>
                <tbody>
                    @forelse ($recentReviews as $review)
                        <tr>
                            <th scope="row"><a href="{{ route('booking.show', $review->booking) }}">#{{ $review->booking_id }}</a></th>
                            <td>{{ $review->booking?->customer?->name ?? '—' }}</td>
                            <td>{{ $review->booking?->driver?->user?->name ?? '—' }}</td>
                            <td class="text-nowrap"><span class="text-warning" aria-hidden="true">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span><span class="visually-hidden">{{ $review->rating }} / 5</span></td>
                            <td class="text-break">{{ $review->comment ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4">No reviews yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $recentReviews->withQueryString()->links('pagination::bootstrap-5') }}

        <h2 class="h5 workspace-heading mt-4">Driver rating summaries</h2>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Driver</th>
                            <th scope="col">Average rating</th>
                            <th scope="col">Ratings</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drivers as $driver)
                            <tr>
                                <td>{{ $driver->user->name }}</td>
                                <td>{{ $driver->average_rating === null ? 'No ratings' : number_format($driver->average_rating, 1).' / 5' }}</td>
                                <td>{{ $driver->reviews_count }}</td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.drivers.reviews.index', $driver) }}">
                                        View ratings
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No drivers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $drivers->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
