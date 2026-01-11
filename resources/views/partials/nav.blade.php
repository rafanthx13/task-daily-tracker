<nav class="mb-4 text-center">
    @if (!empty($prev))
    <button id="btnGetPreviousNextTask" data-old="{{ $prev }}"
        data-today="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
        class="inline-block px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 ml-2 cursor-pointer">
        Carregar dia anterior
    </button>
    @endif

    @if(!request()->routeIs('home') && !request()->is('day/*') && !request()->is(''))
    <a href="{{ url('/') }}"
        class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 ml-2">Tarefas</a>
    @endif

    @if(!request()->routeIs('tags.index') && !request()->is('tags/*'))
    <a href="{{ route('tags.index') }}"
        class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 ml-2">Tags</a>
    @endif

    @if(!request()->routeIs('analytics.index') && !request()->is('analytics/*'))
    <a href="{{ route('analytics.index') }}"
        class="inline-block px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 ml-2">Analytics</a>
    @endif

    @if(!request()->routeIs('reminders.*'))
    <a href="{{ route('reminders.index') }}"
        class="inline-block px-4 py-2 bg-pink-600 text-white rounded hover:bg-pink-700 ml-2 shadow-sm transition">Lembretes</a>
    @endif
</nav>
