<?php

namespace App\Http\Controllers;

use App\Models\DaySummary;
use Illuminate\Http\Request;

class DaySummaryController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'content' => 'nullable|string',
        ]);

        $summary = DaySummary::updateOrCreate(
            ['date' => $request->input('date')],
            ['content' => $request->input('content')]
        );

        return response()->json([
            'success' => true,
            'message' => 'Resumo salvo com sucesso!',
            'summary' => $summary
        ]);
    }
}
