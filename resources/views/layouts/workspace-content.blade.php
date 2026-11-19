@isset($header)
    <header class="container mb-3">{{ $header }}</header>
@endisset
@hasSection('body')
    @yield('body')
@else
    @yield('content', $slot ?? '')
@endif
