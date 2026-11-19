@extends('layouts.dashboard')

@section('page-title', 'Users')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.driver-approvals') }}">Driver approvals</a>
@endsection

@section('content')
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th scope="col">User</th><th scope="col">Role</th><th scope="col">Phone</th><th scope="col">Registration date</th><th scope="col">Status</th></tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <th scope="row">{{ $user->name }}<small class="d-block fw-normal text-body-secondary">{{ $user->email }}</small></th>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>{{ $user->phone ?: '—' }}</td>
                        <td>{{ $user->created_at?->timezone('Asia/Amman')->format('d M Y') ?? '—' }}</td>
                        <td>@include('booking._status', ['state' => $user->is_active ? 'approved' : 'draft', 'label' => $user->is_active ? 'Active' : 'Inactive'])</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4">No users yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
@endsection
