@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">إدارة السائقين</h1>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="card mb-4">
            <div class="card-header">طلبات السائقين المعلقة</div>
            <div class="list-group list-group-flush">
                @forelse ($pendingDrivers as $driver)
                    <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <strong>{{ $driver->name }}</strong>
                            <div class="text-muted">{{ $driver->email }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.users.update', $driver) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="approve_driver" value="1">
                            <button class="btn btn-success" type="submit">موافقة</button>
                        </form>
                    </div>
                @empty
                    <div class="list-group-item text-muted">لا توجد طلبات معلقة.</div>
                @endforelse
            </div>
        </section>

        <section class="card">
            <div class="card-header">السائقون النشطون</div>
            <div class="list-group list-group-flush">
                @forelse ($activeDrivers as $driver)
                    <div class="list-group-item">
                        <strong>{{ $driver->name }}</strong>
                        <span class="text-muted">{{ $driver->email }}</span>
                    </div>
                @empty
                    <div class="list-group-item text-muted">لا يوجد سائقون نشطون.</div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h4>Pending Drivers</h4>
    <table class="table table-bordered mb-4">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>License #</th>
                <th>License Image</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingDrivers as $driver)
                <tr>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->email }}</td>
                    <td>{{ $driver->driverProfile->license_number ?? '-' }}</td>
                    <td>
                        @if($driver->driverProfile && $driver->driverProfile->license_image)
                            <a href="{{ asset('storage/' . $driver->driverProfile->license_image) }}" target="_blank">View</a>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('users.update', $driver->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="approve_driver" value="1">
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form action="{{ route('users.destroy', $driver->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No pending drivers.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h4>Active Drivers</h4>
    <table class="table table-bordered mb-4">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr>
        </thead>
        <tbody>
            @foreach($activeDrivers as $driver)
                <tr>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->email }}</td>
                    <td>{{ $driver->phone }}</td>
                    <td>
                        <form action="{{ route('users.destroy', $driver->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Customers</h4>
    <table class="table table-bordered">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Phone</th></tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
