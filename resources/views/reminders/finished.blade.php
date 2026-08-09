@extends('layout')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-800 border border-transparent dark:border-gray-700 shadow-xl rounded">
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('reminders.sporadic.index') }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            <span class="font-medium">Voltar</span>
        </a>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 flex-1 text-center">Histórico de Lembretes</h1>
        <div class="w-16"></div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <th class="px-6 py-4 text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Título</th>
                    <th class="px-6 py-4 text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Criado em</th>
                    <th class="px-6 py-4 text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Finalizado em</th>
                    <th class="px-6 py-4 text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Duração</th>
                    <th class="px-6 py-4 text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($reminders as $reminder)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition">
                    <td class="px-6 py-4">
                        <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $reminder->title }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $reminder->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-emerald-600 dark:text-emerald-400 font-medium">
                        {{ $reminder->last_completed_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400">
                        {{ $reminder->duration }}
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('reminders.destroy', $reminder->id) }}" method="POST" onsubmit="return confirm('Excluir permanentemente do histórico?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-400 transition p-1 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        Nenhum lembrete finalizado encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
