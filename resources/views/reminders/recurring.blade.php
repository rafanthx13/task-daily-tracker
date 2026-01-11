@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white shadow rounded">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('reminders.index') }}" class="flex items-center text-blue-600 hover:text-blue-800 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            <span class="font-medium">Voltar</span>
        </a>
        <h1 class="text-3xl font-bold text-gray-800 flex-1 text-center">Lembretes Diários</h1>
        <div class="w-16"></div>
    </div>

    <form action="{{ route('reminders.store') }}" method="POST" class="mb-8 bg-gray-50 p-4 rounded-lg flex gap-4">
        @csrf
        <input type="hidden" name="type" value="recurring">
        <input type="text" name="title" placeholder="Hábito diário (ex: Beber água, Meditar)" required
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 shadow-md">
            Adicionar
        </button>
    </form>

    <div class="space-y-3">
        @forelse ($reminders as $reminder)
            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition">
                <span class="text-gray-800 font-medium">{{ $reminder->title }}</span>
                <form action="{{ route('reminders.destroy', $reminder->id) }}" method="POST" onsubmit="return confirm('Remover este hábito diário?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            </div>
        @empty
            <p class="text-center text-gray-500 py-8">Nenhum lembrete diário criado.</p>
        @endforelse
    </div>
</div>
@endsection
