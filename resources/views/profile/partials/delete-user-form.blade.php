<section class="card">
    <div class="card-body">
        <h2 class="h5 workspace-heading">Close account</h2>
        <p class="text-body-secondary">Confirm your password to close your account.</p>

        <details @if ($errors->userDeletion->isNotEmpty()) open @endif>
            <summary class="text-danger fw-semibold">Close account options</summary>
            <form class="mt-3" method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="mb-3">
                    <label class="form-label" for="close-account-password">Password</label>
                    <input id="close-account-password" name="password" type="password" autocomplete="current-password"
                        class="form-control @error('password', 'userDeletion') is-invalid @enderror" required>
                    @error('password', 'userDeletion')
                        <p class="invalid-feedback">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-danger">Close account</button>
            </form>
        </details>
    </div>
</section>
