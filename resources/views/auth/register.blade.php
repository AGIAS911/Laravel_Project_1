@extends('layouts.auth')

@section('title', 'Create your account')
@section('auth-screen', 'register')


@section('content')
    @php($accountType = old('account_type', request()->query('type') === 'driver' ? 'driver' : 'customer'))
    <h1>Create your account</h1>
    <p class="muted">Choose how you’ll use MoveIt, then enter your account details.</p>

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
            <legend class="auth-field-heading">Account type</legend>
            <div class="auth-account-options">
                @foreach ([
                    ['customer', 'Customer', 'Book and track a move.', 'fa-box'],
                    ['driver', 'Driver', 'Apply to accept moving jobs.', 'fa-truck'],
                ] as [$type, $title, $description, $icon])
                    <label class="auth-account-card">
                        <i class="fa-solid {{ $icon }} auth-role-icon" aria-hidden="true"></i>
                        <span><strong id="account-{{ $type }}">{{ $title }}</strong><small id="account-{{ $type }}-hint">{{ $description }}</small></span>
                        <input type="radio" name="account_type" value="{{ $type }}" required
                            aria-labelledby="account-{{ $type }}" aria-describedby="account-{{ $type }}-hint"
                            @checked($accountType === $type)>
                    </label>
                @endforeach
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
                <div class="auth-password-field">
                    <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" aria-describedby="register-password-hint{{ $errors->has('password') ? ' register-password-error' : '' }}" @error('password') aria-invalid="true" @enderror>
                    <button class="auth-password-reveal" type="button" data-password-reveal="password" aria-controls="password" aria-label="Show password" hidden>
                        <i class="fa-regular fa-eye" aria-hidden="true"></i> <span data-password-label>Show</span>
                    </button>
                </div>
                <p class="hint" id="register-password-hint">Use at least 8 characters.</p>
                @error('password') <p id="register-password-error" class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <div class="auth-password-field">
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" @error('password_confirmation') aria-describedby="register-password_confirmation-error" @enderror @error('password_confirmation') aria-invalid="true" @enderror>
                    <button class="auth-password-reveal" type="button" data-password-reveal="password confirmation" aria-controls="password_confirmation" aria-label="Show password confirmation" hidden>
                        <i class="fa-regular fa-eye" aria-hidden="true"></i> <span data-password-label>Show</span>
                    </button>
                </div>
                @error('password_confirmation') <p id="register-password_confirmation-error" class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <fieldset class="product-license-fields" id="driver-fields" @if ($accountType !== 'driver') hidden disabled @endif>
            <legend><i class="fa-regular fa-id-card" aria-hidden="true"></i> Driver license</legend>
            <p class="auth-license-summary">Your application goes to the MoveIt team for approval after you register.</p>
            <div class="field">
                <label for="license_number">License number</label>
                <input id="license_number" name="license_number" maxlength="100" required value="{{ old('license_number') }}" @error('license_number') aria-describedby="register-license_number-error" @enderror @error('license_number') aria-invalid="true" @enderror>
                @error('license_number') <p id="register-license_number-error" class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="license_image">License image</label>
                <input id="license_image" type="file" name="license_image" required accept="image/jpeg,image/png" aria-describedby="license-image-hint @error('license_image') register-license_image-error @enderror" @error('license_image') aria-invalid="true" @enderror>
                @error('license_image') <p id="register-license_image-error" class="field-error">{{ $message }}</p> @enderror
                <p class="hint" id="license-image-hint">Upload a clear JPEG or PNG, up to 2 MB.</p>
            </div>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-block"><span data-auth-submit-label>{{ $accountType === 'driver' ? 'Submit driver application' : 'Create account' }}</span> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>
    <p class="auth-form-footer muted">Already have an account? <a class="link" href="{{ route('login') }}">Log in</a></p>

@endsection
