@extends('layouts.dashboard')

@section('page-title', 'Services')
@section('page-description', 'Transport and add-on catalog.')

@section('actions')
    <a class="btn btn-primary" href="{{ route('services.create') }}">Add service</a>
@endsection

@section('content')
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th scope="col">Service</th><th scope="col">Type</th><th scope="col">Price</th><th scope="col">Unit</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <th scope="row">{{ $service->name }}</th>
                        <td>{{ $service->type === \App\Models\Service::TYPE_TRANSPORT ? 'Basic' : 'Add-on' }}</td>
                        <td>{{ $service->type === \App\Models\Service::TYPE_TRANSPORT ? 'By distance' : number_format((float) $service->price, 3) . ' JOD' }}</td>
                        <td>{{ str($service->unit)->headline() }}</td>
                        <td>@include('booking._status', ['state' => $service->is_active ? 'approved' : 'draft', 'label' => $service->is_active ? 'Active' : 'Inactive'])</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('services.show', $service) }}">Details</a>
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('services.edit', $service) }}">Edit</a>
                            @if ($service->type !== \App\Models\Service::TYPE_TRANSPORT)
                            <form class="d-inline" method="POST" action="{{ route('admin.services.status', $service) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-danger btn-sm" name="is_active" value="{{ $service->is_active ? 0 : 1 }}">{{ $service->is_active ? 'Disable' : 'Enable' }}</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">No services yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $services->links('pagination::bootstrap-5') }}</div>
@endsection
