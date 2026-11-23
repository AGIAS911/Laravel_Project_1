@extends('layouts.moveit-public')

@section('page')
    <div class="auth" data-auth-screen="@yield('auth-screen')">
        <aside class="auth-side" aria-label="About MoveIt">
            <img src="{{ asset('landing/img/room.jpg') }}" alt="" width="800" height="600">
            <div class="product-auth-cover">
                @if (trim($__env->yieldContent('auth-screen')) === 'register')
                    <h2>Move with MoveIt.</h2>
                    <p>Book furniture transport or apply to join as a driver.</p>
                @else
                    <h2>Your move, in one place.</h2>
                    <p>See your quote, pay your deposit, and follow your booking as it progresses.</p>
                @endif
            </div>
        </aside>
        <main class="auth-main">
            <div class="auth-box">
                <a href="{{ url('/') }}" class="logo" aria-label="MoveIt home"><span class="moveit-brand">MOVE<span>IT</span></span></a>
                @yield('content')
            </div>
        </main>
    </div>
    @hasSection('auth-screen')
        <script src="{{ asset('js/auth-form.js') }}" defer></script>
    @endif
@endsection
