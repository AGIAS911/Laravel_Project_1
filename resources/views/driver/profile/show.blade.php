@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card p-4 col-md-8">
        <h4 class="mb-3">Driver Profile</h4>
        <p><strong>Name:</strong> {{ $driver->user->name }}</p>
        <p><strong>Email:</strong> {{ $driver->user->email }}</p>
        <p><strong>License Number:</strong> {{ $driver->license_number }}</p>
        <p><strong>Status:</strong> {{ ucfirst($driver->status) }}</p>

        @if($driver->license_image)
            <div class="mb-3">
                <strong class="d-block mb-2">License Image:</strong>
                <a href="{{ route('drivers.license', $driver) }}" target="_blank" rel="noopener">
                    <img src="{{ route('drivers.license', $driver) }}" width="220" class="rounded border" alt="Driver license">
                </a>
            </div>
        @endif

        <div class="mt-2">
            <a href="{{ route('driver.profile.edit') }}" class="btn btn-warning btn-sm">Edit Profile</a>
            <form action="{{ route('driver.profile.destroy') }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Are you sure you want to disable your account?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Disable Account</button>
            </form>
        </div>
    </div>
</div>
@endsection