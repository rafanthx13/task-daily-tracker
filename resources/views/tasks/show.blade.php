@extends('layout')

@section('content')

<div class="max-w-3xl mx-auto p-6 bg-white dark:bg-gray-800 border border-transparent dark:border-gray-700 shadow-xl rounded mb-6 text-gray-900 dark:text-gray-100">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $task->title }}</h1>
        <a href="{{ route('home') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 flex items-center gap-1 font-medium transition">
            &larr; Voltar para Home
        </a>
    </div>

    <!-- Metrics Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-50 dark:bg-blue-950/40 p-4 rounded-lg border border-blue-100 dark:border-blue-800/60">
            <span class="block text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1">Iniciado em</span>
            <span class="text-lg font-bold text-blue-900 dark:text-blue-200">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</span>
        </div>

        <div class="bg-green-50 dark:bg-green-950/40 p-4 rounded-lg border border-green-100 dark:border-green-800/60">
            <span class="block text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-1">Status Final</span>
            <span class="text-lg font-bold text-green-900 dark:text-green-200">
                @if($endDate)
                Finalizado em {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @else
                Em progresso...
                @endif
            </span>
        </div>

        <div class="bg-purple-50 dark:bg-purple-950/40 p-4 rounded-lg border border-purple-100 dark:border-purple-800/60">
            <span class="block text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider mb-1">Duração Total</span>
            <span class="text-lg font-bold text-purple-900 dark:text-purple-200">{{ $duration }} {{ $duration == 1 ? 'dia' : 'dias' }}</span>
        </div>
    </div>

    <!-- Task Details -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-3 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-800 dark:text-gray-100">Detalhes Atuais</h2>
        <div class="space-y-4 text-gray-700 dark:text-gray-300">
            @if(!empty($task->notes))
            <div>
                <span class="font-semibold block mb-1">Notas:</span>
                <p class="bg-gray-50 dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200">{{ $task->notes }}</p>
            </div>
            @endif

            @if(!empty($task->tags))
            <div>
                <span class="font-semibold block mb-1">Tags:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($task->tags as $tag)
                    @php
                    $tagBaseColor = $tag->color ?? '#E0E7FF';
                    $tagBgColor = $tagBaseColor . '20';
                    $tagBorderColor = $tagBaseColor . '40';
                    $styleTag = "background-color: {$tagBgColor}; color: {$tagBaseColor}; border-color: {$tagBorderColor};";
                    $htmlPropriety = 'style="' . $styleTag . '"';
                    @endphp
                    <span @php echo $htmlPropriety @endphp
                        class="px-2 py-0.5 rounded-full text-xs font-medium border">
                        {{ $tag->name }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Lineage Timeline -->
    <div>
        <h2 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 text-gray-800 dark:text-gray-100">Histórico e Evolução</h2>
        <div class="relative pl-6 border-l-2 border-blue-200 dark:border-blue-800 space-y-6">
            @foreach($family as $fTask)
            <div class="relative">
                <!-- Dot -->
                <div class="absolute -left-[31px] top-1 w-4 h-4 rounded-full border-2 border-white dark:border-gray-800 bg-blue-500 shadow-sm"></div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700 {{ $fTask->id == $task->id ? 'ring-2 ring-blue-400' : '' }}">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($fTask->date)->format('d/m/Y') }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tight
                                {{ $fTask->status == 'done' ? 'bg-green-100 dark:bg-green-950/70 text-green-700 dark:text-green-300' : 'bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300' }}">
                            {{ strtoupper($fTask->status) }}
                        </span>
                    </div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $fTask->title }}</h4>
                    @if(!empty($fTask->notes))
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 italic line-clamp-2">"{{ $fTask->notes }}"</p>
                    @endif
                    @if($fTask->id != $task->id)
                    <a href="{{ route('tasks.show', $fTask->id) }}" class="text-xs text-blue-500 dark:text-blue-400 hover:underline mt-2 inline-block font-medium">
                        Ver esta instância &rarr;
                    </a>
                    @else
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 inline-block font-medium italic">(Instância atual)</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
