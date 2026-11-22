@extends('layouts.app')

@section('body')
    <div class="vehicle-workspace container">
        @if (session('success'))
            <div class="message" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="message error" role="alert">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
@endsection
