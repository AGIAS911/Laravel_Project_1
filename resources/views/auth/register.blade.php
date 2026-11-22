@extends('layouts.auth')

@section('title', 'Create your account')


@section('content')
    @php($accountType = old('account_type', request()->query('type') === 'driver' ? 'driver' : 'customer'))
    <h1>Create your account</h1>
    <p class="muted">Book a move as a customer or apply to drive with MoveIt.</p>

    @if ($errors->any())
        <ul class="alert err" role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    <form method="POST" action="{{ route('register', absolute: false) }}" enctype="multipart/form-data">
        @csrf
        <fieldset class="field">
            <legend class="sm b">Account type</legend>
            <div class="seg">
                <label><input type="radio" name="account_type" value="customer" @checked($accountType === 'customer')> <i class="fa-solid fa-user" aria-hidden="true"></i> Customer</label>
                <label><input type="radio" name="account_type" value="driver" @checked($accountType === 'driver')> <i class="fa-solid fa-truck" aria-hidden="true"></i> Driver</label>
            </div>
        </fieldset>

        <div class="field">
            <label for="name">Full name</label>
            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" @error('name') aria-describedby="register-name-error" @enderror @error('name') aria-invalid="true" @enderror>
            @error('name') <p id="register-name-error" class="field-error">{{ $message }}</p> @enderror
        </div>
        <div class="form-row">
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-describedby="register-email-error" @enderror @error('email') aria-invalid="true" @enderror>
                @error('email') <p id="register-email-error" class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="07XXXXXXXX" @error('phone') aria-describedby="register-phone-error" @enderror @error('phone') aria-invalid="true" @enderror>
                @error('phone') <p id="register-phone-error" class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" @error('password') aria-describedby="register-password-error" @enderror @error('password') aria-invalid="true" @enderror>
                @error('password') <p id="register-password-error" class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" @error('password_confirmation') aria-describedby="register-password_confirmation-error" @enderror @error('password_confirmation') aria-invalid="true" @enderror>
                @error('password_confirmation') <p id="register-password_confirmation-error" class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <fieldset class="product-license-fields" id="driver-fields" @if ($accountType !== 'driver') hidden @endif>
            <legend>Driver license</legend>
            <div class="field">
                <label for="license_number">License number</label>
                <input id="license_number" name="license_number" value="{{ old('license_number') }}" @error('license_number') aria-describedby="register-license_number-error" @enderror @error('license_number') aria-invalid="true" @enderror>
                @error('license_number') <p id="register-license_number-error" class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="license_image">License image</label>
                <input id="license_image" type="file" name="license_image" accept="image/jpeg,image/png" aria-describedby="license-image-hint @error('license_image') register-license_image-error @enderror" @error('license_image') aria-invalid="true" @enderror>
                @error('license_image') <p id="register-license_image-error" class="field-error">{{ $message }}</p> @enderror
                <p class="hint" id="license-image-hint">JPEG or PNG, up to 2 MB. Driver accounts are reviewed after registration.</p>
            </div>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-block">Create account <span class="dot"></span></button>
    </form>
    <p class="center muted sm mt">Already have an account? <a class="link" href="{{ route('login') }}">Log in</a></p>

    <script>
        const driverFields = document.getElementById('driver-fields');
        const accountTypes = document.querySelectorAll('[name="account_type"]');

        function syncDriverFields() {
            const isDriver = document.querySelector('[name="account_type"]:checked')?.value === 'driver';
            driverFields.hidden = !isDriver;
            driverFields.querySelectorAll('input').forEach(input => {
                input.required = isDriver;
                input.disabled = !isDriver;
            });
        }

        accountTypes.forEach(input => input.addEventListener('change', syncDriverFields));
        syncDriverFields();
    </script>
@endsection
