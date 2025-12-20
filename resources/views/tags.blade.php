@extends('layout')

@section('content')

<div class="max-w-4xl mx-auto p-4 md:p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-800">Gerenciar Tags</h1>
        <a href="{{ route('home') }}" class="flex items-center text-blue-600 hover:text-blue-800 transition duration-200 bg-blue-50 px-4 py-2 rounded-lg font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            Voltar
        </a>
    </div>

    @if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm flex items-center" role="alert">
        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <p class="font-medium text-sm">{{ session('success') }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Lado Esquerdo: Adicionar Tag -->
        <div class="md:col-span-1">
            <div class="bg-white p-6 shadow-xl rounded-2xl border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Nova Tag
                </h3>
                <form action="{{ route('tags.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <input type="text" name="name" placeholder="Ex: Prioridade" class="w-full border border-gray-200 px-4 py-2 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" name="color" value="#3B82F6" class="h-10 w-10 border-none rounded cursor-pointer">
                            <span class="text-gray-500 text-xs">Identificador visual</span>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow-lg shadow-blue-200 transition duration-300 transform active:scale-95">
                        Adicionar
                    </button>
                </form>
            </div>
        </div>

        <!-- Lado Direito: Listagem -->
        <div class="md:col-span-2">
            <div class="bg-white p-6 shadow-xl rounded-2xl border border-gray-100 min-h-[300px]">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Tags Existentes
                </h3>

                @if($tags->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <p>Nenhuma tag cadastrada ainda.</p>
                </div>
                @else
                <div class="space-y-4">
                    @foreach ($tags as $tag)
                    <div class="flex flex-col md:flex-row items-center gap-3 bg-gray-50 p-3 rounded-2xl border border-transparent hover:border-blue-100 hover:bg-white transition group">
                        <form action="{{ route('tags.update', $tag) }}" method="POST" class="flex-grow flex flex-col sm:flex-row items-center gap-3 w-full">
                            @csrf
                            @method('PUT')
                            <input type="color" name="color" value="{{ $tag->color ?? '#3B82F6' }}" class="h-8 w-8 border-none rounded cursor-pointer shrink-0">
                            <input type="text" name="name" value="{{ $tag->name }}"
                                class="w-full bg-transparent border-none focus:ring-0 font-medium text-gray-700 placeholder-gray-400 transition" />
                            <button class="bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-1.5 rounded-xl font-bold text-xs uppercase tracking-tight transition opacity-0 group-hover:opacity-100 w-full sm:w-auto">
                                Salvar
                            </button>
                        </form>

                        <form action="{{ route('tags.destroy', $tag) }}" method="POST" class="shrink-0 w-full md:w-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white p-2 rounded-xl transition w-full md:w-auto flex justify-center items-center"
                                onclick="return confirm('Tem certeza que deseja excluir esta tag?')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
