@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card p-4 col-md-6">
        <h4>{{ $driver ? 'Edit Driver Profile' : 'Complete your driver profile' }}</h4>
        @unless ($driver)
            <p>Enter your license details to submit your profile for approval.</p>
        @endunless
        <form action="{{ route('driver.profile.update', absolute: false) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label" for="license_number">License number</label>
                <input type="text" id="license_number" name="license_number" class="form-control" value="{{ old('license_number', $driver?->license_number) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="license_image">{{ $driver ? 'New license image (optional)' : 'License image' }}</label>
                <input type="file" id="license_image" name="license_image" class="form-control" accept="image/jpeg,image/png" @required(!$driver)>
                <p class="form-text">A new license number requires a new photo. Uploading a photo sends the profile back for review.</p>
            </div>

            @foreach ($errors->all() as $message)
                <p class="text-danger" role="alert">{{ $message }}</p>
            @endforeach
            <button type="submit" class="btn btn-primary">{{ $driver ? 'Save' : 'Submit for approval' }}</button>
        </form>
    </div>
</div>
@endsection