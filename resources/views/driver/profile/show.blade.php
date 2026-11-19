@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card p-4">
        <h4>Driver Profile</h4>
        <p><strong>Name:</strong> {{ $driver->user->name }}</p>
        <p><strong>License Number:</strong> {{ $driver->license_number }}</p>
        <p><strong>Status:</strong> {{ $driver->status }}</p>
        @if($driver->license_image)
            <p><img src="{{ asset('storage/' . $driver->license_image) }}" width="200"></p>
        @endif

        <div>
            <a href="{{ route('driver.profile.edit') }}" class="btn btn-warning btn-sm">Edit Profile</a>
            <form action="{{ route('driver.profile.destroy') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete Account</button>
            </form>
        </div>
    </div>
</div>
@endsection