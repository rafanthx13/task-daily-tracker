@extends('layout')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white shadow rounded">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('home') }}" class="flex items-center text-blue-600 hover:text-blue-800 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            <span class="font-medium">Voltar</span>
        </a>
        <h1 class="text-3xl font-bold text-gray-800 flex-1 text-center">Analytics Mensal</h1>
        <div class="w-16"></div> {{-- Spacer to keep title centered --}}
    </div>

    <div class="mb-8 flex flex-col md:flex-row items-center justify-center gap-4 bg-gray-50 p-4 rounded-lg">
        <div>
            <label for="month-select" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Mês/Ano</label>
            <input type="month" id="month-select" value="{{ date('Y-m') }}"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mt-4 md:mt-5">
            <button id="btn-load-analytics" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 cursor-pointer shadow-md">
                Gerar Relatório
            </button>
        </div>
    </div>

    <div id="analytics-results" class="hidden">
        {{-- Summary Cards --}}
        <div class="flex justify-center mb-8">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded shadow-sm w-full md:w-1/3 text-center">
                <p class="text-blue-700 text-sm font-semibold uppercase">Total de Tarefas</p>
                <p id="stat-total" class="text-4xl font-bold text-blue-900">0</p>
                <p class="text-blue-600 text-xs mt-1">Tarefas únicas iniciadas no mês</p>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Lista de Tarefas do Período</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 border-b font-bold text-gray-600">Título</th>
                            <th class="px-4 py-2 border-b font-bold text-gray-600">Data</th>
                            <th class="px-4 py-2 border-b font-bold text-gray-600">Status</th>
                            <th class="px-4 py-2 border-b font-bold text-gray-600">Tags</th>
                        </tr>
                    </thead>
                    <tbody id="tasks-table-body">
                        {{-- Rows will be injected here --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="analytics-loading" class="hidden py-12 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-500">Carregando dados...</p>
    </div>

    <div id="analytics-empty" class="hidden py-12 text-center text-gray-500">
        Nenhuma tarefa encontrada para o mês selecionado.
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/analytics.js') }}"></script>
@endpush
@endsection
