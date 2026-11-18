@extends('layouts.app')

@section('body')
    <div class="container moveit-auth">
        @include('layouts.partials.flash')
        @yield('content')
    </div>
@endsection
