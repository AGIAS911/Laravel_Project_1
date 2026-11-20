@extends('layouts.dashboard')

@php($currentRole = $role ?? null)

@section('page-title', $currentRole === 'customer' ? 'Customers' : ($currentRole === 'driver' ? 'Drivers' : 'Users'))

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('admin.driver-approvals') }}">Driver approvals</a>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <nav class="d-flex flex-wrap gap-2 mb-3" aria-label="Filter users">
        <a @class(['move-filter', 'is-current' => !$currentRole])
           href="{{ route('admin.users.index') }}"
           @if (!$currentRole) aria-current="page" @endif>All users</a>
        <a @class(['move-filter', 'is-current' => $currentRole === 'customer'])
           href="{{ route('admin.users.index', ['role' => 'customer']) }}"
           @if ($currentRole === 'customer') aria-current="page" @endif>Customers</a>
        <a @class(['move-filter', 'is-current' => $currentRole === 'driver'])
           href="{{ route('admin.users.index', ['role' => 'driver']) }}"
           @if ($currentRole === 'driver') aria-current="page" @endif>Drivers</a>
    </nav>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col">User</th>
                    <th scope="col">Role</th>
                    <th scope="col">Phone</th>
                    @if ($currentRole !== 'customer')
                        <th scope="col">License</th>
                    @endif
                    <th scope="col">Registration date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @php($profile = $user->driverProfile)
                    <tr>
                        <th scope="row">
                            {{ $user->name }}
                            <small class="d-block fw-normal text-body-secondary">{{ $user->email }}</small>
                        </th>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>{{ $user->phone ?: '—' }}</td>

                        @if ($currentRole !== 'customer')
                            <td>
                                @if ($user->role === 'driver')
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($profile?->license_image)
                                            <a href="{{ route('drivers.license', $profile) }}" target="_blank" rel="noopener">
                                                <img src="{{ route('drivers.license', $profile) }}"
                                                     alt="License"
                                                     style="width: 52px; height: 34px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                                            </a>
                                        @endif
                                        <div>
                                            <div>{{ $profile?->license_number ?: '—' }}</div>
                                            @if ($profile?->license_image)
                                                <a class="small" href="{{ route('drivers.license', $profile) }}" target="_blank" rel="noopener">View image</a>
                                            @else
                                                <small class="text-body-secondary">No image</small>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        @endif

                        <td class="text-nowrap">{{ $user->created_at?->timezone('Asia/Amman')->format('d M Y') ?? '—' }}</td>
                        <td>@include('booking._status', ['state' => $user->is_active ? 'approved' : 'draft', 'label' => $user->is_active ? 'Active' : 'Inactive'])</td>
                        <td>
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    data-edit-user
                                    data-url="{{ route('admin.users.update', $user) }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-phone="{{ $user->phone }}"
                                    data-role="{{ $user->role }}"
                                    data-license-number="{{ $profile?->license_number }}"
                                    data-license-url="{{ $profile?->license_image ? route('drivers.license', $profile) : '' }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $currentRole !== 'customer' ? 7 : 6 }}" class="text-center py-4">No users yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>

    <dialog class="admin-decision text-start" id="edit-user-dialog" aria-labelledby="edit-user-title" style="max-width: 480px; width: 100%;">
        <h2 id="edit-user-title" class="h5 mb-3">Edit user</h2>
        <form method="POST" enctype="multipart/form-data" id="edit-user-form">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label" for="edit-name">Name</label>
                <input type="text" class="form-control" id="edit-name" name="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="edit-email">Email</label>
                <input type="email" class="form-control" id="edit-email" name="email" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="edit-phone">Phone</label>
                <input type="text" class="form-control" id="edit-phone" name="phone">
            </div>

            <div id="edit-driver-fields" class="d-none border-top pt-3 mt-3">
                <div class="mb-3">
                    <label class="form-label" for="edit-license-number">License number</label>
                    <input type="text" class="form-control" id="edit-license-number" name="license_number">
                </div>

                <div class="mb-2 d-none" id="edit-license-preview-wrap">
                    <span class="form-label d-block">Current license image</span>
                    <a id="edit-license-link" href="#" target="_blank" rel="noopener">
                        <img id="edit-license-preview" src="" alt="Current license" style="max-height: 110px; border-radius: 6px; border: 1px solid #dee2e6;">
                    </a>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="edit-license-image">New license image (optional)</label>
                    <input type="file" class="form-control" id="edit-license-image" name="license_image" accept="image/jpeg,image/png">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" id="close-edit-user">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dialog = document.getElementById('edit-user-dialog');
            const form = document.getElementById('edit-user-form');
            const driverFields = document.getElementById('edit-driver-fields');
            const previewWrap = document.getElementById('edit-license-preview-wrap');
            const previewImg = document.getElementById('edit-license-preview');
            const previewLink = document.getElementById('edit-license-link');
            const fileInput = document.getElementById('edit-license-image');

            document.querySelectorAll('[data-edit-user]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    form.action = btn.dataset.url;
                    document.getElementById('edit-name').value = btn.dataset.name || '';
                    document.getElementById('edit-email').value = btn.dataset.email || '';
                    document.getElementById('edit-phone').value = btn.dataset.phone || '';
                    document.getElementById('edit-license-number').value = btn.dataset.licenseNumber || '';
                    fileInput.value = '';

                    if (btn.dataset.role === 'driver') {
                        driverFields.classList.remove('d-none');
                        if (btn.dataset.licenseUrl) {
                            previewImg.src = btn.dataset.licenseUrl;
                            previewLink.href = btn.dataset.licenseUrl;
                            previewWrap.classList.remove('d-none');
                        } else {
                            previewWrap.classList.add('d-none');
                        }
                    } else {
                        driverFields.classList.add('d-none');
                        previewWrap.classList.add('d-none');
                    }

                    dialog.showModal();
                });
            });

            document.getElementById('close-edit-user').addEventListener('click', () => dialog.close());
        });
    </script>
@endsection