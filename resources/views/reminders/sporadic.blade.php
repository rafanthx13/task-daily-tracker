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
        <h1 class="text-3xl font-bold text-gray-800 flex-1 text-center">Lembretes Esporádicos</h1>
        <a href="{{ route('reminders.finished') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1 bg-blue-50 px-3 py-1 rounded-full transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Histórico
        </a>
        <div class="w-16"></div>
    </div>

    <form action="{{ route('reminders.store') }}" method="POST" class="mb-8 bg-gray-50 p-4 rounded-lg flex gap-4">
        @csrf
        <input type="hidden" name="type" value="sporadic">
        <input type="text" name="title" placeholder="O que você precisa lembrar?" required
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition duration-200 shadow-md">
            Adicionar
        </button>
    </form>

    <div class="space-y-3">
        @forelse ($reminders as $reminder)
            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition group">
                <span class="text-gray-800 font-medium reminder-title">{{ $reminder->title }}</span>
                <div class="flex items-center gap-2">
                    <button class="finish-sporadic-btn text-gray-400 hover:text-emerald-600 p-2 transition cursor-pointer"
                            data-id="{{ $reminder->id }}" title="Finalizar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                    <button class="edit-reminder-btn text-gray-400 hover:text-blue-600 p-2 transition"
                            data-id="{{ $reminder->id }}"
                            data-title="{{ $reminder->title }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <form action="{{ route('reminders.destroy', $reminder->id) }}" method="POST" onsubmit="return confirm('Remover este lembrete?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-700 p-2 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-8">Nenhum lembrete esporádico criado.</p>
        @endforelse
    </div>
</div>

{{-- Modal de Edição --}}
<div id="editReminderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform transition-all">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Editar Lembrete</h2>
        <form id="editReminderForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <input type="text" name="title" id="editReminderTitle" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" class="close-edit-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-md transition cursor-pointer">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(function() {
        $('.edit-reminder-btn').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');

            $('#editReminderTitle').val(title);
            $('#editReminderForm').attr('action', `/reminders/${id}`);
            $('#editReminderModal').removeClass('hidden').addClass('flex');
        });

        $('.close-edit-modal, #editReminderModal').on('click', function(e) {
            if (e.target === this) {
                $('#editReminderModal').addClass('hidden').removeClass('flex');
            }
        });
    });
</script>
@endpush
@endsection
