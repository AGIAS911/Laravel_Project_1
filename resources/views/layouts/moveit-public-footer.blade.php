<footer class="site-footer">
  <div class="container">
    <div class="foot-grid">
      <div><span class="moveit-brand" aria-label="MoveIt">MOVE<span>IT</span></span><p class="muted">A platform connecting customers with trusted moving vehicle owners, with smart inventory and transparent pricing.</p></div>
      <div><h4>Platform</h4><ul><li><a href="{{ route('services.index') }}">Services</a></li><li><a href="{{ route('vehicle-types.index') }}">Vehicle types</a></li><li><a href="{{ route('info.how-it-works') }}">How it works</a></li></ul></div>
      <div><h4>Account</h4><ul><li><a href="{{ route($accountRoute) }}">{{ $accountLabel }}</a></li>@guest
          <li><a href="{{ route('register') }}">Create a customer account</a></li>
          <li><a href="{{ route('register', ['type' => 'driver']) }}">Join as a driver</a></li>
        @endguest</ul></div>
      <div><h4>Support</h4><ul><li><a href="{{ route('info.faq') }}">FAQ</a></li><li><a href="{{ route('info.contact') }}">Contact us</a></li><li><a href="{{ route('info.policies') }}">Policies and terms</a></li></ul></div>
    </div>
    <div class="copy"><span>© 2026 MoveIt. All rights reserved.</span><span>Prices in Jordanian dinars (JOD)</span></div>
  </div>
</footer>
