@extends($dashboardLayout)

@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    <p>{{ __('Welcome, :name.', ['name' => auth()->user()->name]) }}</p>
@endsection
