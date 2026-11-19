<section class="card">
    <div class="card-body">
        <h2 class="h5 workspace-heading">Update password</h2>
        <p class="text-body-secondary">Use a strong password you do not use elsewhere.</p>

        <form method="POST" action="{{ url('/password') }}">
            @csrf
            @method('PUT')

            @foreach (['current_password' => 'Current password', 'password' => 'New password', 'password_confirmation' => 'Confirm new password'] as $field => $label)
                <div class="mb-3">
                    <label class="form-label" for="update-{{ $field }}">{{ $label }}</label>
                    <input class="form-control @error($field, 'updatePassword') is-invalid @enderror" id="update-{{ $field }}" name="{{ $field }}"
                        type="password" autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}" required>
                    @error($field, 'updatePassword')
                        <p class="invalid-feedback">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Save password</button>
            @if (session('status') === 'password-updated')
                <span class="ms-2 text-success" role="status">Saved.</span>
            @endif
        </form>
    </div>
</section>
