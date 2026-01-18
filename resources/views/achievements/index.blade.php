@extends('layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ url('/') }}" class="text-gray-500 hover:text-blue-600 transition" title="Voltar">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Minhas Conquistas 🏆</h1>
        </div>
        <button onclick="toggleForm()" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300 shadow-md cursor-pointer">
            + Adicionar Conquista
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Form to Add Achievement (Hidden by default) -->
    <div id="achievementForm" class="hidden bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-100">
        <form action="{{ route('achievements.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Título</label>
                    <input type="text" name="title" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Período (MM/AAAA)</label>
                    <input type="text" name="period" placeholder="ex: 01/2026" required pattern="\d{2}/\d{4}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="toggleForm()" class="px-6 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition cursor-pointer">Cancelar</button>
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-8 rounded-lg transition shadow-md cursor-pointer">Salvar Conquista</button>
            </div>
        </form>
    </div>

    <!-- Achievements List -->
    <div class="grid gap-8">
        @forelse($groupedAchievements ?? [] as $period => $achievements)
            <div class="mb-4">
                <div class="flex items-center gap-4 mb-4">
                    <h2 class="text-xl font-bold text-yellow-600 bg-yellow-50 px-4 py-1 rounded-full shadow-sm">{{ $period }}</h2>
                    <div class="flex-1 h-px bg-yellow-200"></div>
                </div>

                <div class="grid gap-4">
                    @foreach($achievements as $achievement)
                        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition duration-300">
                            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $achievement->title }}</h3>
                                    <p class="text-gray-600 whitespace-pre-line">{{ $achievement->description }}</p>
                                </div>

                                <div class="flex gap-2 flex-shrink-0">
                                    <button onclick='editAchievement(@json($achievement))' class="text-blue-500 hover:bg-blue-50 p-2 rounded-lg transition cursor-pointer" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <form action="{{ route('achievements.destroy', $achievement) }}" method="POST" onsubmit="return confirm('Tem certeza?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition cursor-pointer" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                <p class="text-gray-500 text-lg italic">Nenhuma conquista registrada ainda. Comece a brilhar! ✨</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center flex p-4 z-50">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-2xl">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Editar Conquista</h2>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Título</label>
                    <input type="text" name="title" id="editTitle" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Período (Não editável)</label>
                    <input type="text" id="editPeriod" disabled class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 italic">
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                <textarea name="description" id="editDescription" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-6 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition cursor-pointer">Cancelar</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded-lg transition shadow-md cursor-pointer">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
// Mask for MM/YYYY
document.querySelector('input[name="period"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
    if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 6);
    }
    e.target.value = value.substring(0, 7); // Limit to MM/YYYY
});

function toggleForm() {
    document.getElementById('achievementForm').classList.toggle('hidden');
}

function editAchievement(achievement) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editForm');

    form.action = `/achievements/${achievement.id}`;
    document.getElementById('editTitle').value = achievement.title;
    document.getElementById('editPeriod').value = achievement.period;
    document.getElementById('editDescription').value = achievement.description || '';

    modal.classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}
</script>
@endsection
