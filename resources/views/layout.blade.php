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
    <script>
        window.APP_URL = "{{ url('/') }}";

        // Apply the theme before rendering to avoid a flash of the wrong color.
        (() => {
            let savedTheme = null;

            try {
                savedTheme = localStorage.getItem('theme');
            } catch (error) {
                // Storage can be unavailable without preventing theme selection.
            }

            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle(
                'dark',
                savedTheme === 'dark' || (savedTheme === null && prefersDark)
            );
        })();
    </script>
    @stack('head') <!-- Para adicionar coisas específicas por página -->
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-6 max-w-3xl mx-auto font-sans transition-colors duration-200">

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-16 right-5 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- Dark Mode Toggle Button -->
    <button id="themeToggle" type="button" aria-label="Alternar modo escuro" aria-pressed="false" title="Ativar modo escuro"
        class="fixed top-5 right-5 z-40 p-2.5 rounded-full bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform active:scale-95 cursor-pointer flex items-center justify-center">
        <!-- Ícone Sol -->
        <svg id="themeToggleSun" class="w-5 h-5 hidden dark:block text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" clip-rule="evenodd"/>
        </svg>
        <!-- Ícone Lua -->
        <svg id="themeToggleMoon" class="w-5 h-5 block dark:hidden text-gray-700" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
        </svg>
    </button>


    @if(request()->routeIs('home') || request()->is('day/*') || request()->is(''))
    @include('partials.header', compact('prev', 'next', 'date'))

    @include('partials.nav', compact('prev', 'next', 'date'))
    @endif

    @yield('content')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        $(function() {
            @if(session('success'))
                showNotification("{{ session('success') }}");
            @endif
            @if(session('error'))
                showNotification("{{ session('error') }}", 'error');
            @endif
        });
    </script>
    @stack('scripts') <!-- Para scripts específicos -->

</body>

</html>
