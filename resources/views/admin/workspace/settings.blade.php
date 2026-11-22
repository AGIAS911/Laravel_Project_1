@extends('layouts.dashboard')

@section('page-title', 'Settings and commission')
@section('page-description', 'Configure booking fees, deposit deadlines and the beneficiary customers see at CliQ checkout.')

@section('content')
    @if (blank(config('booking.osrm_base_url')))
        <div class="alert alert-warning" role="alert">
            Booking quotes are unavailable. Set <code>OSRM_BASE_URL</code> to the routing service.
        </div>
    @endif
    @if (collect($settings)->only(['deposit_percent', 'commission_percent', 'deposit_minutes', 'clik_name', 'clik_id'])->contains(fn ($value) => blank($value)))
        <div class="alert alert-warning" role="alert">
            Complete the deposit and CliQ settings below to enable booking quotes and deposit payments.
        </div>
    @endif
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')

        <div class="row g-4">
            @foreach ([
                'Commission and deposit' => [
                    'commission_percent' => ['Commission (%)', '0', '100', '0.01'],
                    'deposit_percent' => ['Deposit (%)', '0.01', '100', '0.01'],

                ],
                'CliQ beneficiary' => [
                    'clik_name' => ['Beneficiary name'],
                    'clik_id' => ['CliQ alias'],
                    'deposit_minutes' => ['Deposit deadline (minutes)', '1', '10080', '1'],
                ],
            ] as $heading => $fields)
                <div class="col-lg-6">
                    <section class="card card-body h-100 product-config-panel">
                        <h2 class="h5 workspace-heading mb-3">{{ $heading }}</h2>
                        @foreach ($fields as $name => $settingsField)
                            <div class="mb-3">
                                <label class="form-label" for="{{ $name }}">{{ $settingsField[0] }}</label>
                                <input class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}"
                                    value="{{ old($name, $settings[$name]) }}" required
                                    @if (isset($settingsField[1])) type="number" min="{{ $settingsField[1] }}" max="{{ $settingsField[2] }}" step="{{ $settingsField[3] }}"
                                    @else type="text" maxlength="100" @endif>
                                @error($name)<p class="invalid-feedback">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </section>
                </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary mt-4">Save settings</button>
    </form>
@endsection
