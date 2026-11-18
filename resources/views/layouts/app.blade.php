<!doctype html>
@php
    $locale = str_replace('_', '-', app()->getLocale());
    $direction = trim($__env->yieldContent('direction', str($locale)->before('-')->is('ar') ? 'rtl' : 'ltr'));
@endphp
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | @endif MoveIt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ $direction === 'rtl' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="{{ asset('css/moveit.css') }}" rel="stylesheet">
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
    <a class="visually-hidden-focusable moveit-skip" href="#main-content">{{ __('Skip to content') }}</a>
    @include('layouts.partials.navbar')

    <main id="main-content" class="@yield('main-class', 'py-4 py-lg-5')" tabindex="-1">
        @section('body')
            <div class="container">
                @include('layouts.partials.flash')
                @yield('content')
            </div>
        @show
    </main>

    @stack('scripts')
</body>
</html>
