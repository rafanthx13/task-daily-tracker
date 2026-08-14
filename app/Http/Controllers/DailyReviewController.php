<?php

namespace App\Http\Controllers;

use App\Constants\Lanes;
use App\Models\DailyReview;
use App\Models\DaySummary;
use App\Models\Reminder;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyReviewController extends Controller
{
    public function show(string $date): View
    {
        $day = $this->reviewableDay($date);
        $data = $this->reviewData($day);
        $summary = DaySummary::where('date', $day->toDateString())->first();
        $review = DailyReview::where('date', $day->toDateString())->first();

        return view('daily_reviews.show', [
            ...$data,
            'date' => $day,
            'summary' => $summary,
            'review' => $review,
            'prev' => '',
            'next' => '',
        ]);
    }

    public function store(Request $request, string $date): RedirectResponse
    {
        $day = $this->reviewableDay($date);
        $validated = $request->validate([
            'content' => 'nullable|string',
            'mood' => 'nullable|integer|between:1,5',
            'energy' => 'nullable|integer|between:1,5',
        ]);

        DB::transaction(function () use ($day, $validated): void {
            $summary = DaySummary::updateOrCreate(
                ['date' => $day->toDateString()],
                ['content' => $validated['content'] ?? null]
            );

            $review = DailyReview::updateOrCreate(
                ['date' => $day->toDateString()],
                [
                    'mood' => $validated['mood'] ?? null,
                    'energy' => $validated['energy'] ?? null,
                    'reviewed_at' => now(),
                ]
            );

            $review->update([
                'report_markdown' => $this->buildMarkdown($day, $this->reviewData($day), $summary, $review),
            ]);
        });

        return redirect()
            ->route('daily-reviews.show', ['date' => $day->toDateString()])
            ->with('success', 'Revisão diária finalizada e relatório Markdown atualizado.');
    }

    private function reviewableDay(string $date): Carbon
    {
        $day = Carbon::parse($date)->startOfDay();

        abort_if($day->isFuture(), 404);

        return $day;
    }

    /**
     * @return array{completed: \Illuminate\Support\Collection, pending: \Illuminate\Support\Collection, extras: \Illuminate\Support\Collection, reminders: \Illuminate\Support\Collection}
     */
    private function reviewData(Carbon $day): array
    {
        $tasks = Task::whereDate('date', $day->toDateString())
            ->orderBy('ordering')
            ->get();

        return [
            'completed' => $tasks->where('status', Lanes::DONE)->values(),
            'pending' => $tasks->whereIn('status', [Lanes::TODO, Lanes::WAITING])->values(),
            'extras' => $tasks->where('status', Lanes::EXTRA)->values(),
            'reminders' => Reminder::whereNotNull('last_completed_at')
                ->whereDate('last_completed_at', $day->toDateString())
                ->orderBy('last_completed_at')
                ->get(),
        ];
    }

    private function buildMarkdown(Carbon $day, array $data, DaySummary $summary, DailyReview $review): string
    {
        $lines = [
            '# Revisão diária — '.$day->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y'),
            '',
            '## Tarefas concluídas ('.$data['completed']->count().')',
            $this->markdownList($data['completed']->pluck('title')->all()),
            '',
            '## Tarefas pendentes ('.$data['pending']->count().')',
            $this->markdownList($data['pending']->pluck('title')->all()),
            '',
            '## Tarefas extras ('.$data['extras']->count().')',
            $this->markdownList($data['extras']->pluck('title')->all()),
            '',
            '## Lembretes concluídos ('.$data['reminders']->count().')',
            $this->markdownList($data['reminders']->pluck('title')->all()),
            '',
            '## Como foi o dia',
            trim($summary->content ?? '') ?: '_Nenhum resumo registrado._',
            '',
            '## Check-in',
            '- Humor: '.($review->mood ? $review->mood.'/5' : 'não informado'),
            '- Energia: '.($review->energy ? $review->energy.'/5' : 'não informada'),
        ];

        return implode("\n", $lines);
    }

    /**
     * @param array<int, string> $items
     */
    private function markdownList(array $items): string
    {
        if ($items === []) {
            return '- Nenhum registro.';
        }

        return collect($items)
            ->map(fn (string $item) => '- '.$item)
            ->implode("\n");
    }
}
