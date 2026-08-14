<?php

namespace App\Http\Controllers;

use App\Models\DaySummary;
use App\Http\Requests\PreviewDaySummaryRequest;
use App\Http\Requests\StoreDaySummaryRequest;
use Illuminate\Support\Str;

class DaySummaryController extends Controller
{
    use Concerns\RespondsWithJson;

    public function preview(PreviewDaySummaryRequest $request)
    {
        $validated = $request->validated();

        return $this->jsonSuccess([
            'html' => Str::markdown($validated['content'] ?? '', [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }

    public function storeOrUpdate(StoreDaySummaryRequest $request)
    {
        $validated = $request->validated();

        $summary = DaySummary::updateOrCreate(
            ['date' => $validated['date']],
            ['content' => $validated['content'] ?? null]
        );

        return $this->jsonSuccess(['summary' => $summary], 'Resumo salvo com sucesso!');
    }
}
