@extends('layout')

@section('content')
<article class="max-w-3xl mx-auto p-6 bg-white dark:bg-gray-800 border border-transparent dark:border-gray-700 shadow-xl rounded-xl">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6 pb-5 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-start gap-3 min-w-0">
            <a href="{{ route('notes.index') }}" class="mt-1 text-gray-500 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400" title="Voltar às anotações">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 break-words">{{ $note->title }}</h1>
                @if($note->description)
                    <p class="mt-2 text-gray-600 dark:text-gray-300 break-words">{{ $note->description }}</p>
                @endif
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('notes.edit', $note) }}" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold px-4 py-2 rounded-lg transition">Editar</a>
            <form action="{{ route('notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Excluir esta anotação permanentemente?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-lg transition cursor-pointer">Excluir</button>
            </form>
        </div>
    </div>

    @if($note->content)
        <div class="day-summary-markdown text-gray-800 dark:text-gray-100">{!! $contentHtml !!}</div>
    @else
        <p class="py-8 text-center italic text-gray-500 dark:text-gray-400">Esta anotação ainda não possui conteúdo.</p>
    @endif
</article>
@endsection
