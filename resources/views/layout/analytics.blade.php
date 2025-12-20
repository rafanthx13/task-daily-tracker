<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Task Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('head') <!-- Para adicionar coisas específicas por página -->
</head>

<body class="bg-gray-50 p-6 max-w-3xl mx-auto font-sans">

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-5 right-5 z-50 flex flex-col gap-2 pointer-events-none"></div>


    @yield('content')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    @stack('scripts') <!-- Para scripts específicos -->

</body>

</html>
