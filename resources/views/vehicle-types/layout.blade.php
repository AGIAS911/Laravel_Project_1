<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Vehicle Types')</title>
    <style>
        body { margin: 0; background: #f3f4f6; color: #1f2937; font: 16px/1.6 Arial, sans-serif; }
        header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 16px 24px; }
        header a { color: #1d4ed8; margin-right: 16px; text-decoration: none; }
        main { max-width: 1000px; margin: 28px auto; padding: 0 16px; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 16px; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .button { display: inline-block; border: 0; border-radius: 5px; background: #1d4ed8; color: #fff; cursor: pointer; padding: 8px 13px; text-decoration: none; font: inherit; }
        .button.secondary { background: #4b5563; }
        .button.danger { background: #b91c1c; }
        label { display: block; font-weight: bold; margin: 14px 0 5px; }
        input[type="text"], input[type="number"], input[type="file"], select, textarea { box-sizing: border-box; width: 100%; padding: 9px; border: 1px solid #9ca3af; border-radius: 5px; font: inherit; }
        textarea { min-height: 110px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .message { padding: 10px 14px; border-radius: 5px; margin-bottom: 14px; background: #dcfce7; }
        .message.error, .field-error { color: #b91c1c; }
        .field-error { margin: 4px 0; }
        img.thumbnail { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }
        @media (max-width: 650px) { .table-wrap { overflow-x: auto; } }
    </style>
</head>
<body>
    <header>
        <a href="{{ route('vehicles.index') }}">Vehicles</a>
        <a href="{{ route('vehicles.create') }}">Add Vehicle</a>
        <a href="{{ route('vehicle-types.index') }}">Vehicle Types</a>
        <a href="{{ route('vehicle-types.create') }}">Add Vehicle Type</a>
    </header>
    <main>
        @if (session('success'))
            <div class="message" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="message error" role="alert">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
