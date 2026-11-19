@extends('layouts.dashboard')

@section('title', 'Account settings | MoveIt')
@section('page-title', 'Account settings')

@section('content')
    <div class="d-grid gap-4">
        @include('profile.partials.update-profile-information-form')
        @include('profile.partials.update-password-form')
        @include('profile.partials.delete-user-form')
    </div>
@endsection
