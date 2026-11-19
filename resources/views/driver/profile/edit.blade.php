@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card p-4 col-md-6">
        <h4>Edit Driver Profile</h4>
        <form action="{{ route('driver.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">License Number</label>
                <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $driver->license_number) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">New License Image (Optional)</label>
                <input type="file" name="license_image" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection