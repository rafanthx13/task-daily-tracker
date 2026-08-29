<?php

namespace App\Http\Controllers;

use App\Constants\Lanes;
use App\Http\Requests\StoreDailyReviewRequest;
use App\Http\Requests\UpdateDailyReviewSettingsRequest;
use App\Models\DailyReview;
use App\Models\DailyReviewSetting;
use App\Models\DaySummary;
use App\Models\Reminder;
use App\Models\Task;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyReviewController extends Controller
{
    public function show(string $date): View
    {
        $day = $this->reviewableDay($date);

        return view('daily_reviews.show', [
            ...$this->reviewData($day),
            'date' => $day,
            'summary' => DaySummary::where('date', $day->toDateString())->first(),
            'review' => $this->reviewForDay($day),
            'settings' => $this->settingsModel(),
            'prev' => '',
            'next' => '',
        ]);
    }

    public function store(StoreDailyReviewRequest $request, string $date): RedirectResponse
    {
        $day = $this->reviewableDay($date);
        $validated = $request->validated();
        $settings = $this->settingsModel();

        DB::transaction(function () use ($day, $validated, $settings): void {
            $summary = DaySummary::updateOrCreate(
                ['date' => $day->toDateString()],
                ['content' => $validated['content'] ?? null]
            );

            $reviewValues = ['reviewed_at' => now()];

            // When this block is hidden, preserve a check-in already stored for the day.
            if ($settings->show_mood_energy) {
                $reviewValues['mood'] = $validated['mood'] ?? null;
                $reviewValues['energy'] = $validated['energy'] ?? null;
            }

            $review = $this->reviewForDay($day) ?? new DailyReview(['date' => $day->toDateString()]);
            $review->fill($reviewValues);
            $review->save();
            $snapshot = $this->makeSnapshot($day, $this->reviewData($day), $summary, $review, $settings);

            $review->update([
                'report_markdown' => $this->buildMarkdown($snapshot),
                'report_snapshot' => $snapshot,
            ]);
        });

        return redirect()
            ->route('daily-reviews.show', ['date' => $day->toDateString()])
            ->with('success', 'Revisão diária finalizada e relatório atualizado.');
    }

    public function settings(Request $request): View
    {
        return view('daily_reviews.settings', [
            'settings' => $this->settingsModel(),
            'returnTo' => $this->reviewReturnUrl($request->query('return_to')),
        ]);
    }

    public function updateSettings(UpdateDailyReviewSettingsRequest $request): RedirectResponse
    {
        $settings = DailyReviewSetting::query()->first() ?? new DailyReviewSetting();
        $settings->fill($request->safe()->only([
            'show_extra_lane',
            'show_reminders',
            'show_mood_energy',
        ]));
        $settings->save();

        return redirect()
            ->to($this->reviewReturnUrl($request->validated('return_to')) ?? route('daily-reviews.settings'))
            ->with('success', 'Configurações da revisão diária atualizadas.');
    }

    public function export(string $date): Response
    {
        $day = $this->reviewableDay($date);
        $review = $this->reviewForDay($day);

        abort_unless($review?->report_markdown, 404);

        $snapshot = $review->report_snapshot
            ?? $this->makeSnapshot(
                $day,
                $this->reviewData($day),
                DaySummary::where('date', $day->toDateString())->first() ?? new DaySummary(),
                $review,
                $this->settingsModel()
            );

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('daily_reviews.pdf', ['snapshot' => $snapshot])->render(), 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="revisao-'.$day->toDateString().'.pdf"',
        ]);
    }

    private function reviewableDay(string $date): Carbon
    {
        $day = Carbon::parse($date)->startOfDay();
        abort_if($day->isFuture(), 404);

        return $day;
    }

    /**
     * @return array{completed: \Illuminate\Support\Collection, pending: \Illuminate\Support\Collection, inProgress: \Illuminate\Support\Collection, extras: \Illuminate\Support\Collection, reminders: \Illuminate\Support\Collection}
     */
    private function reviewData(Carbon $day): array
    {
        $tasks = Task::whereDate('date', $day->toDateString())->orderBy('ordering')->get();

        return [
            'completed' => $tasks->where('status', Lanes::DONE)->values(),
            'pending' => $tasks->where('status', Lanes::TODO)->values(),
            'inProgress' => $tasks->where('status', Lanes::WAITING)->values(),
            'extras' => $tasks->where('status', Lanes::EXTRA)->values(),
            'reminders' => Reminder::whereNotNull('last_completed_at')
                ->whereDate('last_completed_at', $day->toDateString())
                ->orderBy('last_completed_at')
                ->get(),
        ];
    }

    private function settingsModel(): DailyReviewSetting
    {
        return DailyReviewSetting::query()->first() ?? new DailyReviewSetting([
            'show_extra_lane' => true,
            'show_reminders' => false,
            'show_mood_energy' => true,
        ]);
    }

    private function reviewForDay(Carbon $day): ?DailyReview
    {
        return DailyReview::whereDate('date', $day->toDateString())->first();
    }

    private function reviewReturnUrl(mixed $returnTo): ?string
    {
        if (! is_string($returnTo)) {
            return null;
        }

        $path = parse_url($returnTo, PHP_URL_PATH);

        if (! is_string($path) || ! preg_match('#^/day/(\d{4}-\d{2}-\d{2})/review$#', $path, $matches)) {
            return null;
        }

        return route('daily-reviews.show', ['date' => $matches[1]]);
    }

    private function makeSnapshot(Carbon $day, array $data, DaySummary $summary, DailyReview $review, DailyReviewSetting $settings): array
    {
        $taskGroups = [
            ['label' => 'Concluídas', 'tasks' => $data['completed']->pluck('title')->all()],
            ['label' => 'Pendentes', 'tasks' => $data['pending']->pluck('title')->all()],
            ['label' => 'Em andamento', 'tasks' => $data['inProgress']->pluck('title')->all()],
        ];

        if ($settings->show_extra_lane && $data['extras']->isNotEmpty()) {
            $taskGroups[] = ['label' => 'Extras', 'tasks' => $data['extras']->pluck('title')->all()];
        }

        return [
            'date_label' => $day->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y'),
            'task_groups' => $taskGroups,
            'show_reminders' => $settings->show_reminders,
            'reminders' => $settings->show_reminders ? $data['reminders']->pluck('title')->all() : [],
            'content' => $summary->content ?? '',
            'show_mood_energy' => $settings->show_mood_energy,
            'mood' => $review->mood,
            'energy' => $review->energy,
            'reviewed_at' => $review->reviewed_at?->locale('pt_BR')->translatedFormat('d/m/Y \\à\\s H:i') ?? now()->locale('pt_BR')->translatedFormat('d/m/Y \\à\\s H:i'),
        ];
    }

    private function buildMarkdown(array $snapshot): string
    {
        $lines = ['# Revisão diária — '.$snapshot['date_label']];

        foreach ($snapshot['task_groups'] as $group) {
            $lines[] = '';
            $lines[] = '## '.$group['label'].' ('.count($group['tasks']).')';
            $lines[] = $this->markdownList($group['tasks']);
        }

        if ($snapshot['show_reminders']) {
            $lines[] = '';
            $lines[] = '## Lembretes concluídos ('.count($snapshot['reminders']).')';
            $lines[] = $this->markdownList($snapshot['reminders']);
        }

        $lines[] = '';
        $lines[] = '## Como foi o dia';
        $lines[] = trim($snapshot['content']) ?: '_Nenhum resumo registrado._';

        if ($snapshot['show_mood_energy']) {
            $lines[] = '';
            $lines[] = '## Check-in';
            $lines[] = '- Humor: '.($snapshot['mood'] ? $snapshot['mood'].'/5' : 'não informado');
            $lines[] = '- Energia: '.($snapshot['energy'] ? $snapshot['energy'].'/5' : 'não informada');
        }

        return implode("\n", $lines);
    }

    /** @param array<int, string> $items */
    private function markdownList(array $items): string
    {
        return $items === []
            ? '- Nenhum registro.'
            : collect($items)->map(fn (string $item) => '- '.$item)->implode("\n");
    }
}
