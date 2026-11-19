@extends('layouts.dashboard')

@section('title', 'Edit booking')
@section('page-title', 'Edit booking')

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('booking.index') }}">{{ __('My bookings') }}</a>
@endsection

@section('content')
    @include('booking._steps', ['current' => 'locations'])
    @include('booking._draft-form', ['draft' => $booking])
@endsection
