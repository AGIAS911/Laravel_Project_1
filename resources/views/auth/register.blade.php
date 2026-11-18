@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                    name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="account_type" class="col-md-4 col-form-label text-md-end">
                                {{ __('Account type') }}
                            </label>
                            <div class="col-md-6">
                                <select id="account_type"
                                    class="form-select @error('account_type') is-invalid @enderror"
                                    name="account_type" required>
                                    <option value="customer" @selected(old('account_type', 'customer') === 'customer')>
                                        {{ __('Customer') }}
                                    </option>
                                    <option value="driver" @selected(old('account_type') === 'driver')>
                                        {{ __('Driver') }}
                                    </option>
                                </select>
                                @error('account_type')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div id="driver-fields" @if (old('account_type') !== 'driver') hidden @endif>
                            <div class="row mb-3">
                                <label for="license_number" class="col-md-4 col-form-label text-md-end">
                                    {{ __('License number') }}
                                </label>
                                <div class="col-md-6">
                                    <input id="license_number" type="text"
                                        class="form-control @error('license_number') is-invalid @enderror"
                                        name="license_number" value="{{ old('license_number') }}">
                                    @error('license_number')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="license_image" class="col-md-4 col-form-label text-md-end">
                                    {{ __('License image') }}
                                </label>
                                <div class="col-md-6">
                                    <input id="license_image" type="file"
                                        class="form-control @error('license_image') is-invalid @enderror"
                                        name="license_image" accept=".jpg,.jpeg,.png">
                                    @error('license_image')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required autocomplete="new-password">
                                @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control"
                                    name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">{{ __('Register') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const accountType = document.getElementById('account_type');
    const driverFields = document.getElementById('driver-fields');
    const licenseNumber = document.getElementById('license_number');
    const licenseImage = document.getElementById('license_image');

    function updateDriverFields() {
        const isDriver = accountType.value === 'driver';
        driverFields.hidden = !isDriver;
        licenseNumber.required = isDriver;
        licenseImage.required = isDriver;
    }

    accountType.addEventListener('change', updateDriverFields);
    updateDriverFields();
</script>
@endsection
