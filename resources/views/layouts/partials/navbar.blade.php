<header class="moveit-header">
    <nav class="container d-flex flex-wrap align-items-center justify-content-between gap-3 py-3" aria-label="{{ __('Main navigation') }}">
        <a class="moveit-brand" href="{{ url('/') }}">Move<span>It</span></a>

        <div class="d-flex flex-wrap align-items-center gap-3">
            @guest
                @if (Route::has('login'))
                    <a class="moveit-nav-link" href="{{ route('login') }}">{{ __('Log in') }}</a>
                @endif
                @if (Route::has('register'))
                    <a class="btn btn-primary" href="{{ route('register') }}">{{ __('Create account') }}</a>
                @endif
            @else
                <a class="moveit-nav-link" href="{{ route('home') }}">{{ __('Dashboard') }}</a>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle text-break" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">{{ __('Log out') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endguest
        </div>
    </nav>
</header>
