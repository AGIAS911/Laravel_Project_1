@extends('layouts.app')

@section('content')
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