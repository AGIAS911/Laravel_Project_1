@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <h4 class="mb-3">Register</h4>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Account Type</label>
                        <select name="account_type" id="account_type" class="form-select" required>
                            <option value="customer" {{ old('account_type') == 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="driver" {{ old('account_type') == 'driver' ? 'selected' : '' }}>Driver</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                    </div>

                    <div id="driver-fields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">License Number</label>
                            <input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">License Image</label>
                            <input type="file" name="license_image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const select = document.getElementById('account_type');
    const driverFields = document.getElementById('driver-fields');

    function toggleDriverFields() {
        driverFields.style.display = select.value === 'driver' ? 'block' : 'none';
    }

    select.addEventListener('change', toggleDriverFields);
    toggleDriverFields();
</script>
@endsection