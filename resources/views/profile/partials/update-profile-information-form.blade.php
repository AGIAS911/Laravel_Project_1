<section class="card">
    <div class="card-body">
        <h2 class="h5 workspace-heading">Profile information</h2>
        <p class="text-body-secondary">Update your name and email address.</p>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            @foreach (['name' => ['Name', 'text', 'name'], 'email' => ['Email address', 'email', 'email']] as $field => [$label, $type, $autocomplete])
                <div class="mb-3">
                    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                    <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}"
                        type="{{ $type }}" value="{{ old($field, $user->{$field}) }}" autocomplete="{{ $autocomplete }}" required>
                    @error($field)
                        <p class="invalid-feedback">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Save profile</button>
            @if (session('status') === 'profile-updated')
                <span class="ms-2 text-success" role="status">Saved.</span>
            @endif
        </form>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                @csrf
                <p class="mb-2">Your email address is unverified.</p>
                <button type="submit" class="btn btn-outline-secondary">Resend verification email</button>
            </form>
            @if (session('status') === 'verification-link-sent')
                <p class="mt-2 text-success" role="status">A new verification link was sent.</p>
            @endif
        @endif
    </div>
</section>
