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
                    <h2 class="text-xl font-semibold mb-3">{{ $lista }}</h2>
                    <ul class="lista" id="lista-{{ strtolower(str_replace(' ', '-', $lista)) }}">
                        @php
                            // dd($tasks);
                        @endphp
                        @foreach ($tasks[$lista] ?? [] as $task)
                            @php
                                // dd($task);
                            @endphp
                            <li class="card">{{ $task['title'] }}</li>
                        @endforeach

                    </ul>
                </section>
            @endforeach

        </div>

        <!-- Modal Adicionar Task -->
        <div id="modalAddCard" title="Adicionar Card" style="display:none;">
            <form id="formAddCard" method="POST" action="{{ route('tasks.store') }}">
                @csrf
                <div class="mb-4">
                    <label for="title" class="block font-semibold mb-1">Título do Card</label>
                    <input type="text" name="title" id="title" class="w-full border rounded px-2 py-1" required />
                </div>
                <input type="hidden" name="status" value="TODO" />
                <label for="tag_id" class="block font-semibold mb-1">Tag</label>
                <select name="tag_id" id="tag_id" class="w-full border rounded px-2 py-1">
                    @foreach(\App\Models\Tag::all() as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Adicionar</button>
            </form>
        </div>


@endsection
