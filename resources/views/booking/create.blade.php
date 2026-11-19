@extends('layouts.dashboard')

@section('title', 'New booking')
@section('page-title', 'New booking')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('booking.index') }}">{{ __('My bookings') }}</a>
@endsection

@section('content')
    @include('booking._steps', ['current' => 'locations'])
    @include('booking._draft-form', ['draft' => null])
@endsection
