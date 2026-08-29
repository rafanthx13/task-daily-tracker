@extends('layout')

@section('content')
<div class="mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Revisão diária</p>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Configurações</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Escolha quais informações aparecem no encerramento e no PDF das próximas revisões.</p>
        </div>
        <a href="{{ $returnTo ?? route('home') }}" class="shrink-0 rounded-lg border border-gray-300 px-4 py-2 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">&larr; Voltar</a>
    </div>

    <form method="POST" action="{{ route('daily-reviews.settings.update') }}" class="space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="return_to" value="{{ old('return_to', $returnTo) }}">
        @foreach ([
            ['name' => 'show_extra_lane', 'label' => 'Mostrar tarefas extras', 'description' => 'Exibe a seção Extras quando houver tarefas nessa raia.'],
            ['name' => 'show_reminders', 'label' => 'Mostrar lembretes concluídos', 'description' => 'Exibe os lembretes concluídos naquela data.'],
            ['name' => 'show_mood_energy', 'label' => 'Mostrar Humor e Energia', 'description' => 'Os dois campos sempre são exibidos ou ocultados juntos.'],
        ] as $option)
            <label class="flex cursor-pointer items-center justify-between gap-5 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40">
                <span>
                    <span class="block font-bold text-gray-800 dark:text-gray-100">{{ $option['label'] }}</span>
                    <span class="mt-1 block text-sm text-gray-600 dark:text-gray-300">{{ $option['description'] }}</span>
                </span>
                <span class="relative inline-flex shrink-0 items-center">
                    <input type="hidden" name="{{ $option['name'] }}" value="0">
                    <input type="checkbox" name="{{ $option['name'] }}" value="1" class="peer sr-only" @checked((bool) old($option['name'], $settings->{$option['name']}))>
                    <span aria-hidden="true" class="h-6 w-11 rounded-full bg-gray-300 transition after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:bg-blue-600 peer-checked:after:translate-x-5 dark:bg-gray-600"></span>
                </span>
            </label>
        @endforeach
        <button type="submit" class="rounded-lg bg-blue-600 px-6 py-3 font-bold text-white shadow-md transition hover:bg-blue-700">Salvar configurações</button>
    </form>
</div>
@endsection
