@extends('layout')

@section('content')

<div class="max-w-3xl mx-auto p-6 bg-white shadow rounded">

    <!-- Botão que abre modal para adicionar card -->
    <div class="mb-6 text-center">
        <button id="btnAddCard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded cursor-pointer">
            Adicionar Task
        </button>
        <button id="btnSeePreviousDay" class="bg-blue-400 hover:bg-blue-500 text-white px-4 py-2 rounded cursor-pointer">
            Ver dia Anterior
        </button>
    </div>

    <div class="flex space-x-6" id="kanban-container">

        <div id="previous-day-kanban-column" class="flex-1 hidden"></div>

        <div id="today-kanban-column" class="flex-1 space-y-6">

            {{-- Seção de Lembretes --}}
            @if($sporadicReminders->isNotEmpty() || $recurringReminders->isNotEmpty())
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-100 shadow-sm">
                @if($recurringReminders->isNotEmpty())
                <div class="mb-4">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Hábitos Diários</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($recurringReminders as $reminder)
                        <button class="complete-recurring-tag px-3 py-1 bg-white text-blue-600 rounded-full text-xs font-semibold border border-blue-100 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition duration-200 cursor-pointer shadow-sm"
                                data-id="{{ $reminder->id }}" title="Clique para concluir hoje">
                            # {{ $reminder->title }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($sporadicReminders->isNotEmpty())
                <div>
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Lembretes</h3>
                    <ul class="grid grid-cols-1 gap-1">
                        @foreach($sporadicReminders as $reminder)
                        <li class="text-sm text-gray-600 flex items-start gap-2 bg-white p-2 rounded border border-gray-50">
                            <span class="text-emerald-500 mt-0.5">📌</span>
                            <span class="leading-tight">{{ $reminder->title }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @endif

            @foreach ($listas as $lista)
            <section>
                <h2 class="text-xl font-semibold mb-3">{{ strtoupper($lista) }}</h2>
                <ul class="lista" id="lista-{{ strtolower(str_replace(' ', '-', $lista)) }}">
                    @foreach ($tasks[$lista] ?? [] as $task)
                    <li class="card p-3 bg-white rounded shadow mb-2" data-id="{{ $task['id'] }}" data-tags="{{ json_encode(collect($task['tags'])->pluck('id')) }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('tasks.show', $task['id']) }}" class="hover:underline">
                                        <h3 class="font-bold text-gray-800">{{ $task['title'] }}</h3>
                                    </a>
                                    @if (!empty($task['tags']))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($task['tags'] as $tag)
                                        @php
                                        $tagBaseColor = $tag['color'] ?? '#E0E7FF';
                                        $tagTextColor = $tag['color'] ?? '#3730A3';
                                        $tagBgColor = $tagBaseColor . '20';
                                        $tagBorderColor = $tagBaseColor . '40';
                                        $styleTag = "background-color: {$tagBgColor}; color: {$tagTextColor}; border-color: {$tagBorderColor};";
                                        $htmlPropriety = 'style="' . $styleTag . '"';
                                        // dd($htmlPropriety);
                                        @endphp
                                        <span
                                            @php
                                            echo $htmlPropriety;
                                            @endphp
                                            class="px-2 py-0.5 rounded-full text-xs font-medium border">
                                            {{ $tag['name'] }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>

                                @if (!empty($task['notes']))
                                <p class="text-gray-600 text-sm mt-1">{{ $task['notes'] }}</p>
                                @endif
                            </div>

                            <!-- Botão de edição -->
                            <button class="edit-task text-gray-500 hover:text-blue-600 cursor-pointer"
                                title="Editar">
                                ✏️
                            </button>
                        </div>
                    </li>
                    @endforeach

                </ul>
            </section>
            @endforeach
        </div>

    </div>

    <!-- Modal Adicionar Task -->
    <div id="modalAddCard" title="Adicionar Card" style="display:none;">
        <hr style="color: lightgray;">
        <form id="formAddCard" method="POST" action="{{ route('tasks.store') }}"
            class="space-y-4 p-4 bg-white rounded-lg">
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
                <input type="date" name="date" id="date" value="{{ $dateStr }}"
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

            {{-- Campo tags --}}
            <div>
                <label for="tag_ids" class="block font-semibold mb-1 text-gray-700">Tags</label>
                <select name="tag_ids[]" id="tag_ids" multiple
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 h-24">
                    @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Pressione Ctrl (ou Cmd) para selecionar várias.</p>
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

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tags</label>
                    <select id="editTaskTags" multiple class="mt-1 w-full border rounded p-2 h-24">
                        @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pressione Ctrl (ou Cmd) para selecionar várias.</p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" id="closeModal"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded cursor-pointer">Cancelar</button>
                    <button id="deleteTaskForm"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 cursor-pointer">Excluir</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded cursor-pointer">Salvar</button>
                </div>
            </form>


        </div>
    </div>

    @endsection
