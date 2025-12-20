<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskAnalyticsController extends Controller
{
    /**

     * Exibe a view principal de analytics
     */
    public function indexView()
    {
        return view('analytics.index');
    }

    /**
     * Retorna os dados para o relatório mensal via AJAX
     */
    public function monthReportData(Request $request)
    {
        $monthYear = $request->get('month'); // Ex: '2025-12'

        if (!$monthYear) {
            return response()->json(['error' => 'Mês não informado'], 400);
        }

        $date = Carbon::parse($monthYear . '-01');
        $year = $date->year;
        $month = $date->month;

        // 1. Apenas as tarefas ORIGINAIS do mês (ignorando duplicadas e extras)
        $originalTasks = Task::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereNull('id_original')
            ->where('status', '!=', \App\Constants\Lanes::EXTRA)
            ->with('tags')
            ->orderBy('date')
            ->get();

        $originalIds = $originalTasks->pluck('id');

        if ($originalIds->isEmpty()) {
            return response()->json([
                'tasks' => [],
                'summary' => [
                    'total' => 0,
                    'by_status' => []
                ]
            ]);
        }

        // 2. Busca todos os membros dessas famílias para encontrar o status mais recente
        $allFamilyMembers = Task::whereIn('id_original', $originalIds)
            ->orWhereIn('id', $originalIds)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // 3. Agrupa por família e pega o primeiro (mais recente) de cada
        $latestMembers = $allFamilyMembers->groupBy(function ($item) {
            return $item->id_original ?? $item->id;
        })->map(function ($group) {
            return $group->first();
        });

        // 4. Modifica os objetos originais com o status atualizado para exibição
        $processedTasks = $originalTasks->map(function ($task) use ($latestMembers) {
            $latest = $latestMembers->get($task->id);
            if ($latest) {
                $task->status = $latest->status;
            }
            return $task;
        });

        // 5. Agrupamento por status atualizado para o resumo
        $summary = $processedTasks->groupBy('status')->map(function ($group) {
            return $group->count();
        });

        return response()->json([
            'tasks' => $processedTasks,
            'summary' => [
                'total' => $processedTasks->count(),
                'by_status' => $summary
            ]
        ]);
    }
}
