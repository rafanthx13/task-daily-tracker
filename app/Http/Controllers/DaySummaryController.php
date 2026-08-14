<?php

namespace App\Http\Controllers;

use App\Models\DaySummary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DaySummaryController extends Controller
{
    use Concerns\RespondsWithJson;

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'content' => 'nullable|string',
        ]);

        return $this->jsonSuccess([
            'html' => Str::markdown($validated['content'] ?? '', [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }

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

        return $this->jsonSuccess(['summary' => $summary], 'Resumo salvo com sucesso!');
    }
}
