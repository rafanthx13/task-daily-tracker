<header class="mb-1 flex items-center justify-center relative">

    @if (!empty($prev))
        <!-- Seta à esquerda -->
        <a href="/day/{{ $prev }}" class="absolute left-0 flex items-center text-blue-600 hover:text-blue-800">
            <!-- Ícone de seta -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
    @endif

    <!-- Título -->
    <h1 class="text-3xl font-bold mb-4 text-center">
        {{ 'Daily Task Tracker' }} - {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
    </h1>

    @if (!empty($next))

        @if($next == \Carbon\Carbon::now()->format('Y-m-d'))
            @php
                $nextUrl = "/";
            @endphp
        @else
            @php
                $nextUrl = '/day' . '/' . $next;
            @endphp
        @endif

        <!-- Seta à direita -->
        <a href="{{ $nextUrl }}" class="absolute right-0 flex items-center text-blue-600 hover:text-blue-800">
            <!-- Ícone de seta -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    @else
        @if (\Carbon\Carbon::parse($date)->format('Y-m-d') != \Carbon\Carbon::now()->format('Y-m-d'))
            <a href="/" class="absolute right-0 flex items-center text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @endif

    @endif

</header>
