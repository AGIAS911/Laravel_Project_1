@extends('layouts.dashboard')

@section('title', 'Account settings | MoveIt')
@section('page-title', 'Account settings')
@section('page-icon', 'fa-user-gear')
@section('page-description', 'Manage your profile and sign-in details.')

@section('content')
    <div class="row g-4 align-items-start">
        <div class="col-lg-6">@include('profile.partials.update-profile-information-form')</div>
        <div class="col-lg-6 d-grid gap-4">
            @include('profile.partials.update-password-form')
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@endsection
