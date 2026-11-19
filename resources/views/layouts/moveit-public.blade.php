<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | MoveIt</title>
    <link rel="icon" href="{{ asset('landing/img/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('landing/css/style.css') }}">
    <script src="{{ asset('landing/js/site-navigation.js') }}" defer></script>
</head>
<body>
    @yield('page')
</body>
</html>
