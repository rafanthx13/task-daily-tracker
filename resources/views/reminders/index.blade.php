@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white dark:bg-gray-800 border border-transparent dark:border-gray-700 shadow-xl rounded">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ url('/') }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            <span class="font-medium">Voltar</span>
        </a>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 flex-1 text-center">Lembretes</h1>
        <div class="w-16"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <a href="{{ route('reminders.recurring.index') }}" class="block p-8 bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-800/60 rounded-xl hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900/60 transition duration-300 text-center group">
            <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300">🔄</div>
            <h2 class="text-xl font-bold text-blue-800 dark:text-blue-300 mb-2">Diários Recorrentes</h2>
            <p class="text-blue-600 dark:text-blue-400 text-sm">Tarefas que se repetem todos os dias como tags na home.</p>
        </a>

        <a href="{{ route('reminders.sporadic.index') }}" class="block p-8 bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-200 dark:border-emerald-800/60 rounded-xl hover:border-emerald-500 dark:hover:border-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition duration-300 text-center group">
            <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300">📌</div>
            <h2 class="text-xl font-bold text-emerald-800 dark:text-emerald-300 mb-2">Esporádicos</h2>
            <p class="text-emerald-600 dark:text-emerald-400 text-sm">Lista de pendências que ficam na home até serem removidas.</p>
        </a>

        <a href="{{ route('reminders.finished') }}" class="block p-8 bg-purple-50 dark:bg-purple-950/40 border-2 border-purple-200 dark:border-purple-800/60 rounded-xl hover:border-purple-500 dark:hover:border-purple-500 hover:bg-purple-100 dark:hover:bg-purple-900/60 transition duration-300 text-center group">
            <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300">📜</div>
            <h2 class="text-xl font-bold text-purple-800 dark:text-purple-300 mb-2">Histórico</h2>
            <p class="text-purple-600 dark:text-purple-400 text-sm">Veja todos os lembretes esporádicos que você já finalizou.</p>
        </a>
    </div>
</div>
@endsection
