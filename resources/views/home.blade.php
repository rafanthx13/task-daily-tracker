@extends('layout')

@section('content')

    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">

        <!-- Botão que abre modal para adicionar card -->
        <div class="mb-6 text-center">
            <button id="btnAddCard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded cursor-pointer">
                Adicionar Task
            </button>
        </div>

        <div class="space-y-6">
            @foreach ($listas as $lista)
                <section>
                    <h2 class="text-xl font-semibold mb-3">{{ strtoupper($lista) }}</h2>
                    <ul class="lista" id="lista-{{ strtolower(str_replace(' ', '-', $lista)) }}">
                        @foreach ($tasks[$lista] ?? [] as $task)
                            <li class="card p-3 bg-white rounded shadow mb-2" data-id="{{ $task['id'] }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-bold text-gray-800">{{ $task['title'] }}</h3>

                                        @if (!empty($task['notes']))
                                            <p class="text-gray-600 text-sm mt-1">{{ $task['notes'] }}</p>
                                        @endif

                                        @if (!empty($task['tags']))
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach ($task['tags'] as $tag)
                                                    <span
                                                        class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">
                                                        {{ $tag['name'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                    </div>

                                    <!-- Botão de edição -->
                                    <button class="edit-task text-gray-500 hover:text-blue-600 cursor-pointer" title="Editar">
                                        ✏️
                                    </button>
                                </div>
                            </li>
                        @endforeach

                    </ul>
                </section>
            @endforeach

        </div>

        <!-- Modal Adicionar Task -->
        <div id="modalAddCard" title="Adicionar Card" style="display:none;">
            <form id="formAddCard" method="POST" action="{{ route('tasks.store') }}"
                class="space-y-4 p-4 bg-white rounded-lg shadow-md">
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
                    <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}"
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
                        @if ($tags->isEmpty())
                            <option value="">Sem tags</option>
                        @else
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Botão --}}
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition cursor-pointer">
                        Adicionar
                    </button>
                </div>
            </form>
        </div>

        <!-- Modal  de Editar Task-->
        <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-bold mb-4">Editar Task</h2>

                <form id="editTaskForm">
                    <input type="hidden" id="editTaskId">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Título</label>
                        <input type="text" id="editTaskTitle" class="mt-1 w-full border rounded p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notas</label>
                        <textarea id="editTaskNotes" class="mt-1 w-full border rounded p-2"></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded cursor-pointer">Cancelar</button>
                        <button id="deleteTaskForm"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 cursor-pointer">Excluir</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded cursor-pointer">Salvar</button>
                    </div>
                </form>


            </div>
        </div>

    @endsection
