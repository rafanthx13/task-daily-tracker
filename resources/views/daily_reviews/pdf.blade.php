<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 26mm 20mm; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.55; }
        h1 { color: #111827; font-size: 23px; margin: 0 0 4px; }
        h2 { color: #1d4ed8; font-size: 14px; margin: 0 0 8px; }
        .eyebrow { color: #2563eb; font-size: 9px; font-weight: bold; letter-spacing: 1.4px; margin: 0 0 10px; text-transform: uppercase; }
        .date { color: #6b7280; font-size: 12px; margin: 0; }
        .divider { border: 0; border-top: 1px solid #dbeafe; margin: 20px 0; }
        .section { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 6px; margin: 0 0 12px; padding: 12px 14px; }
        ul { margin: 0; padding-left: 18px; }
        li { margin: 3px 0; }
        .empty { color: #6b7280; font-style: italic; margin: 0; }
        .checkin { width: 100%; border-collapse: collapse; }
        .checkin td { border-top: 1px solid #e5e7eb; padding: 7px 0; }
        .checkin td:last-child { font-weight: bold; text-align: right; }
        .footer { color: #6b7280; font-size: 9px; margin-top: 18px; text-align: center; }
    </style>
</head>
<body>
    <p class="eyebrow">Encerramento do dia</p>
    <h1>Revisão diária</h1>
    <p class="date">{{ $snapshot['date_label'] }}</p>
    <hr class="divider">
    @foreach ($snapshot['task_groups'] as $group)
        <section class="section">
            <h2>{{ $group['label'] }} ({{ count($group['tasks']) }})</h2>
            @if (count($group['tasks']))
                <ul>@foreach ($group['tasks'] as $task)<li>{{ $task }}</li>@endforeach</ul>
            @else
                <p class="empty">Nenhuma tarefa.</p>
            @endif
        </section>
    @endforeach
    @if (($snapshot['show_reminders'] ?? false) === true)
        <section class="section">
            <h2>Lembretes concluídos ({{ count($snapshot['reminders']) }})</h2>
            @if (count($snapshot['reminders']))
                <ul>@foreach ($snapshot['reminders'] as $reminder)<li>{{ $reminder }}</li>@endforeach</ul>
            @else
                <p class="empty">Nenhum lembrete concluído registrado para esta data.</p>
            @endif
        </section>
    @endif
    <section class="section">
        <h2>Resumo e reflexão</h2>
        <div>{!! nl2br(e($snapshot['content'] ?: 'Nenhum resumo registrado.')) !!}</div>
    </section>
    @if (($snapshot['show_mood_energy'] ?? false) === true)
        <section class="section">
            <h2>Check-in</h2>
            <table class="checkin">
                <tr><td>Humor</td><td>{{ $snapshot['mood'] ? $snapshot['mood'].'/5' : 'Não informado' }}</td></tr>
                <tr><td>Energia</td><td>{{ $snapshot['energy'] ? $snapshot['energy'].'/5' : 'Não informada' }}</td></tr>
            </table>
        </section>
    @endif
    <p class="footer">Revisão salva em {{ $snapshot['reviewed_at'] }}</p>
</body>
</html>
