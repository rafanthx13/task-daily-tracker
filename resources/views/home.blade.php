@extends('layout') <!-- Se tiver um layout padrão -->
@section('content')

    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">

        <!-- Botão que abre modal para adicionar card -->
        <div class="mb-6 text-center">
            <button id="btnAddCard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Adicionar Card na TODO
            </button>
        </div>

        <div class="space-y-6">
            @php
                // dd($listas);
            @endphp
            @foreach ($listas as $lista)
                <section>
                    <h2 class="text-xl font-semibold mb-3">{{ strtoupper($lista) }}</h2>
                    <ul class="lista" id="lista-{{ strtolower(str_replace(' ', '-', $lista)) }}">
                        @php
                        @endphp
                        @foreach ($tasks[$lista] ?? [] as $task)
                            @php
                          //  <li class="card" >{{ $task['title'] }}</li>
                            @endphp

                            <li class="card p-3 bg-white rounded shadow mb-2" data-id="{{ $task['id'] }}">
                                <h3 class="font-bold text-gray-800">{{ $task['title'] }}</h3>

                                @if (!empty($task['notes']))
                                    <p class="text-gray-600 text-sm mt-1">{{ $task['notes'] }}</p>
                                @endif

                                @if (!empty($task['tags']))
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach ($task['tags'] as $tag)
                                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">
                                                {{ $tag['name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endforeach

                    </ul>
                </section>
            @endforeach

        </div>

        <!-- Modal Adicionar Task -->
    <div id="modalAddCard" title="Adicionar Card" style="display:none;">
    <form id="formAddCard" method="POST" action="{{ route('tasks.store') }}" class="space-y-4 p-4 bg-white rounded-lg shadow-md">
        @csrf

        {{-- Campo título --}}
        <div>
            <label for="title" class="block font-semibold mb-1 text-gray-700">Título do Card</label>
            <input type="text" name="title" id="title"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Digite o título" required />
        </div>

        {{-- Campo data --}}
        <div>
            <label for="date" class="block font-semibold mb-1 text-gray-700">Data</label>
            <input type="date" name="date" id="date"
                   value="{{ date('Y-m-d') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required />
        </div>

        {{-- Campo notas --}}
        <div>
            <label for="notes" class="block font-semibold mb-1 text-gray-700">Notas</label>
            <textarea name="notes" id="notes" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Escreva observações opcionais..."></textarea>
        </div>

        {{-- Status oculto --}}
        <input type="hidden" name="status" value="todo" />

        {{-- Campo tag --}}
        <div>
            <label for="tag_id" class="block font-semibold mb-1 text-gray-700">Tag</label>
            <select name="tag_id" id="tag_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @php
                    $tags = \App\Models\Tag::all();
                @endphp
                @if($tags->isEmpty())
                    <option value="">Sem tags</option>
                @else
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        {{-- Botão --}}
        <div class="flex justify-end">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition">
                Adicionar
            </button>
        </div>
    </form>
</div>



@endsection
