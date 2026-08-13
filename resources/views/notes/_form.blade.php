@csrf

<div class="mb-5">
    <label for="title" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Título</label>
    <input id="title" name="title" type="text" maxlength="255" required autofocus
        value="{{ old('title', $note->title ?? '') }}"
        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500">
    @error('title') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
</div>

<div class="mb-5">
    <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Breve descrição</label>
    <textarea id="description" name="description" rows="2" maxlength="500"
        class="note-auto-resize w-full resize-none overflow-hidden px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500"
        placeholder="Um resumo curto para identificar esta anotação na listagem.">{{ old('description', $note->description ?? '') }}</textarea>
    @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
</div>

<div class="mb-6">
    <div class="flex items-center justify-between gap-4 mb-2">
        <label for="content" class="block text-sm font-bold text-gray-700 dark:text-gray-300">Conteúdo</label>
        <span class="text-xs text-gray-500 dark:text-gray-400">Aceita Markdown</span>
    </div>
    <textarea id="content" name="content" rows="12"
        class="note-auto-resize w-full resize-none overflow-hidden px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500"
        placeholder="# Minha anotação&#10;&#10;Escreva livremente usando Markdown...">{{ old('content', $note->content ?? '') }}</textarea>
    @error('content') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
</div>

<div class="flex flex-wrap justify-end gap-3">
    <a href="{{ isset($note) ? route('notes.show', $note) : route('notes.index') }}"
        class="px-5 py-2.5 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancelar</a>
    <button type="submit"
        class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold px-6 py-2.5 rounded-lg shadow-md transition cursor-pointer">
        {{ $submitLabel }}
    </button>
</div>

@push('scripts')
<script>
    $(function() {
        function resizeNoteTextarea() {
            this.style.height = 'auto';
            this.style.height = `${this.scrollHeight}px`;
        }

        $('.note-auto-resize').each(resizeNoteTextarea).on('input', resizeNoteTextarea);
    });
</script>
@endpush
