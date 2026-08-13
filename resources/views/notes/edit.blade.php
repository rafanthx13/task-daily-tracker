@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white dark:bg-gray-800 border border-transparent dark:border-gray-700 shadow-xl rounded-xl">
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('notes.show', $note) }}" class="text-gray-500 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400" title="Voltar">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Editar anotação</h1>
    </div>

    <form action="{{ route('notes.update', $note) }}" method="POST">
        @method('PUT')
        @include('notes._form', ['submitLabel' => 'Salvar alterações'])
    </form>
</div>
@endsection
