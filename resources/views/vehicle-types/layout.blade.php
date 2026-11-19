@extends('layouts.app')

@push('styles')
    <style>
        .vehicle-workspace .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; padding: 20px; margin-bottom: 16px; }
        .vehicle-workspace .actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .vehicle-workspace .button { display: inline-block; border: 0; border-radius: 12px; background: #c9343d; color: #fff; cursor: pointer; padding: 8px 13px; text-decoration: none; font: inherit; }
        .vehicle-workspace .button.secondary { background: #4b5563; }
        .vehicle-workspace .button.danger { background: #b91c1c; }
        .vehicle-workspace label { display: block; font-weight: bold; margin: 14px 0 5px; }
        .vehicle-workspace input[type="text"], .vehicle-workspace input[type="number"], .vehicle-workspace input[type="file"], .vehicle-workspace select, .vehicle-workspace textarea { box-sizing: border-box; width: 100%; padding: 9px; border: 1px solid #9ca3af; border-radius: 12px; font: inherit; }
        .vehicle-workspace textarea { min-height: 110px; }
        .vehicle-workspace table { width: 100%; border-collapse: collapse; }
        .vehicle-workspace th, .vehicle-workspace td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .vehicle-workspace .message { padding: 10px 14px; border-radius: 12px; margin-bottom: 14px; background: #dcfce7; }
        .vehicle-workspace .message.error, .vehicle-workspace .field-error { color: #b91c1c; }
        .vehicle-workspace .field-error { margin: 4px 0; }
        .vehicle-workspace img.thumbnail { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }
        @media (max-width: 650px) { .vehicle-workspace .table-wrap { overflow-x: auto; } }
    </style>
@endpush

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
