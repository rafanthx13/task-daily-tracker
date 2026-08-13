@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white dark:bg-gray-800 border border-transparent dark:border-gray-700 shadow-xl rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="text-gray-500 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400" title="Voltar às tarefas">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Anotações</h1>
        </div>
        <a href="{{ route('notes.create') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold px-5 py-2.5 rounded-lg shadow-md transition">
            + Nova anotação
        </a>
    </div>

    <div class="grid gap-4">
        @forelse($notes as $note)
            <a href="{{ route('notes.show', $note) }}"
                class="block p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 hover:border-cyan-400 dark:hover:border-cyan-600 hover:shadow-md transition group">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 group-hover:text-cyan-700 dark:group-hover:text-cyan-300 transition break-words">{{ $note->title }}</h2>
                        @if($note->description)
                            <p class="mt-2 text-gray-600 dark:text-gray-300 break-words">{{ $note->description }}</p>
                        @endif
                    </div>
                    <span class="text-cyan-600 dark:text-cyan-400 text-xl" aria-hidden="true">→</span>
                </div>
            </a>
        @empty
            <div class="py-14 px-6 text-center rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                <p class="text-lg text-gray-500 dark:text-gray-400">Nenhuma anotação criada ainda.</p>
                <a href="{{ route('notes.create') }}" class="inline-block mt-4 text-cyan-700 dark:text-cyan-300 font-bold hover:underline">Criar a primeira anotação</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
