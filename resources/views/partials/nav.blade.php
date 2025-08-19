<nav class="mb-4 text-center">
    @if (!empty($prev))
        <button id="btnGetPreviousNextTask" data-old="{{ $prev }}"
            data-today="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
            class="inline-block px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 ml-2 cursor-pointer">
            Carregar dia anterior
        </button>
    @endif
    <a href="{{ route('home') }}"
        class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 ml-2">Tarefas</a>
    <a href="{{ route('tags.index') }}"
        class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 ml-2">Tags</a>
</nav>
