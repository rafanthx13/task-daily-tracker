@extends('layout') <!-- Se tiver um layout padrão -->
@section('content')
<div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
    <h2 class="text-2xl font-bold mb-4">Gerenciar Tags</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('tags.store') }}" method="POST" class="mb-6 flex gap-2">
        @csrf
        <input type="text" name="name" placeholder="Nova tag" class="border px-3 py-2 rounded w-full" required>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Adicionar</button>
    </form>

    <ul class="space-y-2">
        @foreach($tags as $tag)
            <li class="flex justify-between items-center border p-2 rounded">
                <form action="{{ route('tags.update', $tag) }}" method="POST" class="flex-grow flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $tag->name }}" class="border px-2 py-1 rounded w-full" />
                    <button class="bg-yellow-500 text-white px-3 py-1 rounded">Atualizar</button>
                </form>
                <form action="{{ route('tags.destroy', $tag) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="bg-red-600 text-white px-3 py-1 rounded ml-2">Excluir</button>
                </form>
            </li>
        @endforeach
    </ul>
</div>
@endsection
