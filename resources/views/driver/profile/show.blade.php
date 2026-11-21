@extends('layouts.app')

@section('title', 'Driver profile | MoveIt')

@section('content')
    @php
        [$statusLabel, $statusIcon, $statusTone] = match ($driver->status) {
            'approved' => ['Approved', 'fa-circle-check', 'approved'],
            'pending' => ['Under review', 'fa-clock', 'pending'],
            'rejected' => ['Rejected', 'fa-circle-xmark', 'restricted'],
            'suspended' => ['Suspended', 'fa-circle-pause', 'restricted'],
            default => [str($driver->status)->headline(), 'fa-circle-info', 'neutral'],
        };
    @endphp

    <div class="driver-profile-page">
        <header class="driver-profile-heading">
            <h1>Driver profile</h1>
            <a class="driver-button driver-fleet-add" href="{{ route('driver.profile.edit') }}">
                <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit profile
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <section class="driver-profile-summary" aria-labelledby="driver-profile-name">
            <div class="driver-profile-avatar" aria-hidden="true">{{ str($user->name)->trim()->substr(0, 1)->upper() }}</div>
            <div class="driver-profile-identity">
                <h2 id="driver-profile-name">{{ $user->name }}</h2>
                <span @class(['driver-profile-badge', 'is-'.$statusTone])>
                    <i class="fa-solid {{ $statusIcon }}" aria-hidden="true"></i><span class="visually-hidden">Driver status</span> {{ $statusLabel }}
                </span>
                @if ($driver->status_reason && $driver->status !== 'approved')
                    <p>{{ $driver->status_reason }}</p>
                @endif
            </div>
        </section>

        <div class="driver-profile-grid">
            <section class="driver-profile-panel" aria-labelledby="driver-contact-heading">
                <h2 id="driver-contact-heading"><i class="fa-regular fa-address-card" aria-hidden="true"></i> Contact details</h2>
                <dl class="driver-profile-facts">
                    <div><dt>Email address</dt><dd>{{ $user->email }}</dd></div>
                    <div><dt>Phone number</dt><dd>{{ $user->phone ?: 'Not provided' }}</dd></div>
                </dl>
            </section>

            <section class="driver-profile-panel" aria-labelledby="driver-license-heading">
                <h2 id="driver-license-heading"><i class="fa-regular fa-id-card" aria-hidden="true"></i> Driver license</h2>
                <dl class="driver-profile-facts">
                    <div><dt>License number</dt><dd><bdi>{{ $driver->license_number ?: 'Not provided' }}</bdi></dd></div>
                </dl>

                @if ($hasLicensePhoto)
                    <a class="driver-button driver-fleet-edit driver-profile-document" href="{{ route('drivers.license', $driver) }}" target="_blank" rel="noopener">
                        <i class="fa-regular fa-image" aria-hidden="true"></i> View license photo
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        <span class="visually-hidden">(opens in a new tab)</span>
                    </a>
                @else
                    <p class="driver-profile-document-note"><i class="fa-regular fa-image" aria-hidden="true"></i> No license photo available.</p>
                @endif
            </section>
        </div>

        <details class="driver-profile-panel driver-profile-account">
            <summary>
                <span class="driver-profile-account-icon"><i class="fa-solid fa-user-lock" aria-hidden="true"></i></span>
                <span><strong>Disable account</strong><span class="driver-profile-account-description">Manage access to your driver account.</span></span>
                <i class="fa-solid fa-chevron-down driver-profile-account-arrow" aria-hidden="true"></i>
            </summary>
            <div class="driver-profile-account-body">
                <p>Disabling your account signs you out and prevents you from signing back in.</p>
                <form action="{{ route('driver.profile.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="driver-button driver-profile-disable">Confirm disable account</button>
                </form>
            </div>
        </details>
    </div>
@endsection
