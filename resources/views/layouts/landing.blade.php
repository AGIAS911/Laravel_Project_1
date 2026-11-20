
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" href="{{ asset('landing/img/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('landing/css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chatbot-widget.css') }}">
    <script src="{{ asset('landing/js/landing.js') }}" defer></script>
</head>
<body>
    @yield('content')
    @include('chatbot.widget')
</body>
</html>
