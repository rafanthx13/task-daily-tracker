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

        // Apenas as tarefas ORIGINAIS do mês (ignorando duplicadas)
        $tasks = Task::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereNull('id_original')
            ->with('tags')
            ->orderBy('date')
            ->get();

        // Agrupamento por status para o resumo
        $summary = $tasks->groupBy('status')->map(function ($group) {
            return $group->count();
        });

        return response()->json([
            'tasks' => $tasks,
            'summary' => [
                'total' => $tasks->count(),
                'by_status' => $summary
            ]
        ]);
    }
}
