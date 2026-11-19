<header class="site-header">
  <div class="container">
    <a class="logo" href="{{ url('/') }}" aria-label="MoveIt"><span class="moveit-brand" aria-label="MoveIt">MOVE<span>IT</span></span></a>
    <nav id="public-menu" class="navpill" aria-label="Main navigation"><a href="{{ url('/') }}">Home</a><a href="{{ route('services.index') }}" @class(['active' => request()->routeIs('services.index')]) @if (request()->routeIs('services.index')) aria-current="page" @endif>Services</a><a href="{{ route('vehicle-types.index') }}" @class(['active' => request()->routeIs('vehicle-types.index')]) @if (request()->routeIs('vehicle-types.index')) aria-current="page" @endif>Vehicles</a><a href="{{ route('info.how-it-works') }}" @class(['active' => request()->routeIs('info.how-it-works')]) @if (request()->routeIs('info.how-it-works')) aria-current="page" @endif>How it works</a><a href="{{ route('info.faq') }}" @class(['active' => request()->routeIs('info.faq')]) @if (request()->routeIs('info.faq')) aria-current="page" @endif>FAQ</a><a href="{{ route('info.contact') }}" @class(['active' => request()->routeIs('info.contact')]) @if (request()->routeIs('info.contact')) aria-current="page" @endif>Contact us</a></nav>
    <div class="head-actions">
      
      <a class="btn btn-outline btn-sm" href="{{ route($accountRoute) }}">{{ $accountLabel }}</a>
      @if (!auth()->check() || auth()->user()->role === 'customer')
        <a class="btn btn-primary" href="{{ route($bookingRoute) }}">Start booking <span class="dot"></span></a>
      @endif
    </div>
    <button class="nav-toggle" type="button" aria-label="Menu" aria-controls="public-menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
  </div>
</header>
