@extends('layout')

@section('content')
@php
    $taskGroups = [
        [
            'label' => 'Concluídas',
            'tasks' => $completed,
            'container' => 'border-emerald-100 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/30',
            'heading' => 'text-emerald-900 dark:text-emerald-100',
        ],
        [
            'label' => 'Pendentes',
            'tasks' => $pending,
            'container' => 'border-amber-100 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/30',
            'heading' => 'text-amber-900 dark:text-amber-100',
        ],
        [
            'label' => 'Extras',
            'tasks' => $extras,
            'container' => 'border-purple-100 bg-purple-50 dark:border-purple-900 dark:bg-purple-950/30',
            'heading' => 'text-purple-900 dark:text-purple-100',
        ],
    ];
@endphp
<div class="mx-auto max-w-4xl rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Encerramento do dia</p>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Revisão de {{ $date->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y') }}</h1>
        </div>
        <a href="{{ $date->isToday() ? route('home') : route('tasks.day', ['date' => $date->toDateString()]) }}" class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            &larr; Voltar ao dia
        </a>
    </div>

    <p class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-100">
        O texto abaixo é o mesmo “Resumo do Dia” da home. Ao finalizar a revisão, ele será salvo nos dois contextos sem duplicação.
    </p>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($taskGroups as $group)
            <section class="rounded-lg border p-4 {{ $group['container'] }}">
                <h2 class="font-bold {{ $group['heading'] }}">{{ $group['label'] }} ({{ $group['tasks']->count() }})</h2>
                <ul class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    @forelse ($group['tasks'] as $task)
                        <li>{{ $task->title }}</li>
                    @empty
                        <li class="italic text-gray-500 dark:text-gray-400">Nenhuma tarefa.</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>

    <section class="mt-6 rounded-lg border border-pink-100 bg-pink-50 p-4 dark:border-pink-900 dark:bg-pink-950/30">
        <h2 class="font-bold text-pink-900 dark:text-pink-100">Lembretes concluídos ({{ $reminders->count() }})</h2>
        <ul class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300">
            @forelse ($reminders as $reminder)
                <li>{{ $reminder->title }}</li>
            @empty
                <li class="italic text-gray-500 dark:text-gray-400">Nenhum lembrete concluído registrado para esta data.</li>
            @endforelse
        </ul>
    </section>

    <form method="POST" action="{{ route('daily-reviews.store', ['date' => $date->toDateString()]) }}" class="mt-6 space-y-5">
        @csrf
        <div>
            <label for="review-content" class="mb-2 block font-bold text-gray-800 dark:text-gray-100">Resumo e reflexão</label>
            <textarea id="review-content" name="content" rows="7" placeholder="Registre como foi o dia..." class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('content', $summary->content ?? '') }}</textarea>
            @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="mood" class="mb-2 block font-bold text-gray-800 dark:text-gray-100">Humor (opcional)</label>
                <select id="mood" name="mood" class="w-full rounded-lg border border-gray-300 bg-white p-3 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Não informar</option>
                    @for ($value = 1; $value <= 5; $value++)
                        <option value="{{ $value }}" @selected((string) old('mood', $review->mood ?? '') === (string) $value)>{{ $value }}/5</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="energy" class="mb-2 block font-bold text-gray-800 dark:text-gray-100">Energia (opcional)</label>
                <select id="energy" name="energy" class="w-full rounded-lg border border-gray-300 bg-white p-3 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Não informar</option>
                    @for ($value = 1; $value <= 5; $value++)
                        <option value="{{ $value }}" @selected((string) old('energy', $review->energy ?? '') === (string) $value)>{{ $value }}/5</option>
                    @endfor
                </select>
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-blue-600 px-6 py-3 font-bold text-white shadow-md transition hover:bg-blue-700">
            {{ $review ? 'Atualizar revisão' : 'Finalizar revisão' }}
        </button>
    </form>

    @if ($review?->report_markdown)
        <details class="mt-8 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <summary class="cursor-pointer font-bold text-gray-800 dark:text-gray-100">Relatório Markdown gerado</summary>
            <pre class="mt-4 overflow-x-auto whitespace-pre-wrap rounded bg-gray-50 p-4 text-sm text-gray-800 dark:bg-gray-900 dark:text-gray-100">{{ $review->report_markdown }}</pre>
        </details>
    @endif
</div>
@endsection
