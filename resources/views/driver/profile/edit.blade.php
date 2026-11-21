@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card p-4 col-md-6">
        <h4 class="mb-3">{{ $driver ? 'Edit Driver Profile' : 'Complete your driver profile' }}</h4>
        @unless ($driver)
            <p>Enter your license details to complete your profile.</p>
        @endunless

        <form action="{{ route('driver.profile.update', absolute: false) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr class="my-4">

            <div class="mb-3">
                <label class="form-label" for="license_number">License number</label>
                <input type="text" id="license_number" name="license_number" class="form-control @error('license_number') is-invalid @enderror"
                       value="{{ old('license_number', $driver?->license_number) }}" required>
                @error('license_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if ($driver?->license_image)
                <div class="mb-3">
                    <span class="form-label d-block">Current license image</span>
                    <a href="{{ route('drivers.license', $driver) }}" target="_blank" rel="noopener">
                        <img src="{{ route('drivers.license', $driver) }}" width="180" class="rounded border" alt="Current driver license">
                    </a>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label" for="license_image">{{ $driver ? 'New license image (optional)' : 'License image' }}</label>
                <input type="file" id="license_image" name="license_image" class="form-control @error('license_image') is-invalid @enderror"
                       accept="image/jpeg,image/png" @required(!$driver)>
                <p class="form-text">A new license number requires a new photo. Submitting a photo sends your license to the admin for review.</p>
                @error('license_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr class="my-4">
            <h6 class="mb-3">Change password (optional)</h6>

            <div class="mb-3">
                <label class="form-label" for="current_password">Current password</label>
                <input type="password" id="current_password" name="current_password"
                       class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">New password</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Confirm new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" autocomplete="new-password">
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">{{ $driver ? 'Save changes' : 'Submit' }}</button>
                @if ($driver)
                    <a href="{{ route('driver.profile.show') }}" class="btn btn-outline-secondary">Cancel</a>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection