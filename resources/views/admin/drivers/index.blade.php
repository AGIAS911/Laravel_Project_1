@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h1 class="h3 mb-4">Driver ratings</h1>

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
            {{ $drivers->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
