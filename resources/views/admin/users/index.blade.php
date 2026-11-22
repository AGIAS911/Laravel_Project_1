@extends('layouts.dashboard')

@section('page-title', 'Driver approvals')
@section('page-description', 'Review submitted licenses and update driver access.')
@section('page-description', 'Review licenses and update driver status.')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <nav class="d-flex flex-wrap gap-2 mb-3" aria-label="Driver status">
        @foreach ($tabs as $value => $label)
            <a @class(['move-filter', 'is-current' => $status === $value]) href="{{ route('admin.driver-approvals', ['status' => $value]) }}"
                @if ($status === $value) aria-current="page" @endif>{{ $label }}{{ $value === 'pending' ? ' ('.$pendingCount.')' : '' }}</a>
        @endforeach
    </nav>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th scope="col">Driver</th><th scope="col">License</th><th scope="col">Vehicles</th><th scope="col">Request date</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
            <tbody>
                @forelse ($drivers as $driver)
                    @php($profile = $driver->driverProfile)
                    <tr>
                        <th scope="row">
                            <div class="d-flex align-items-center gap-2">
                                <span class="admin-avatar flex-shrink-0" aria-hidden="true">{{ str($driver->name)->substr(0, 1)->upper() }}</span>
                                <span>{{ $driver->name }}<small class="d-block text-body-secondary fw-normal">{{ $driver->phone ?: '—' }}</small></span>
                            </div>
                        </th>
                        <td>@if ($profile->license_image)<a href="{{ route('drivers.license', $profile) }}" target="_blank" rel="noopener">View image</a>@else — @endif</td>
                        <td>{{ $profile->vehicles_count }}</td>
                        <td class="text-nowrap">{{ $profile->created_at?->timezone('Asia/Amman')->format('d M') ?? '—' }}</td>
                        <td>@include('booking._status', ['state' => $status, 'label' => $status === 'pending' ? 'Pending approval' : ucfirst($status)])</td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                @if ($status !== 'approved')
                                    <form method="POST" action="{{ route('admin.users.driver-status', $driver) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success btn-sm" name="status" value="approved">Approve</button>
                                    </form>
                                @endif
                                @if ($status === 'pending' || $status === 'approved')
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-driver-dialog
                                        data-url="{{ route('admin.users.driver-status', $driver) }}"
                                        data-status="{{ $status === 'pending' ? 'rejected' : 'suspended' }}">{{ $status === 'pending' ? 'Reject' : 'Stop' }}</button>
                                @endif
                            </div>
                            @if ($profile->status_reason)<small class="text-body-secondary">{{ $profile->status_reason }}</small>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">No drivers with this status.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $drivers->links('pagination::bootstrap-5') }}</div>

    <dialog class="admin-decision" id="driver-decision" aria-labelledby="driver-decision-title">
        <div class="admin-decision-icon" aria-hidden="true">!</div>
        <h2 id="driver-decision-title">Reject driver</h2>
        <p>The driver will see the reason.</p>
        <form method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status">
            <textarea class="form-control mb-4" name="reason" aria-label="Reason" rows="4" maxlength="1000" placeholder="Enter a reason..." required></textarea>
            <button type="button" class="btn btn-secondary" data-close-decision>Cancel</button>
            <button type="submit" class="btn btn-danger" data-submit-decision>Reject</button>
        </form>
    </dialog>
@endsection
