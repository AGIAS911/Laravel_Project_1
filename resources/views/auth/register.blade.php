@extends('layouts.auth')

@section('title', 'Create your account')
@section('auth-heading')
    Join<br>MoveIt Today
@endsection

@section('content')
    @php($accountType = old('account_type', request()->query('type') === 'driver' ? 'driver' : 'customer'))
    <h1>Create your account</h1>
    <p class="muted" style="margin:6px 0 24px">Choose an account type to get started.</p>

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
            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
        </div>
        <div class="form-row">
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="07XXXXXXXX">
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
            </div>
        </div>

        <div id="driver-fields" @if ($accountType !== 'driver') hidden @endif>
            <div class="field">
                <label for="license_number">License number</label>
                <input id="license_number" name="license_number" value="{{ old('license_number') }}">
            </div>
            <div class="field">
                <label for="license_image">License image</label>
                <input id="license_image" type="file" name="license_image" accept="image/jpeg,image/png" aria-describedby="license-image-hint">
                <p class="hint" id="license-image-hint">JPEG or PNG, up to 2 MB. Driver accounts are reviewed after registration.</p>
            </div>
        </div>
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
