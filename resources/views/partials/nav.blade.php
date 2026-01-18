<nav class="mb-4 text-center">
    @if(!request()->routeIs('home') && !request()->is('day/*') && !request()->is(''))
    <a href="{{ url('/') }}"
        class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 ml-2 cursor-pointer">Tarefas</a>
    @endif

    @if(!request()->routeIs('tags.index') && !request()->is('tags/*'))
    <a href="{{ route('tags.index') }}"
        class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 ml-2 cursor-pointer">Tags</a>
    @endif

    @if(!request()->routeIs('analytics.index') && !request()->is('analytics/*'))
    <a href="{{ route('analytics.index') }}"
        class="inline-block px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 ml-2 cursor-pointer">Analytics</a>
    @endif

    @if(!request()->routeIs('reminders.*'))
    <a href="{{ route('reminders.index') }}"
        class="inline-block px-4 py-2 bg-pink-600 text-white rounded hover:bg-pink-700 ml-2 shadow-sm transition cursor-pointer">Lembretes</a>
    @endif

    @if(!request()->routeIs('achievements.*'))
    <a href="{{ route('achievements.index') }}"
        class="inline-block px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 ml-2 shadow-sm transition cursor-pointer">Conquistas</a>
    @endif
</nav>
