<?php

namespace App\Http\Controllers;

use App\Models\TimeManagementEntry;
use App\Models\TimeManagementTag;
use App\Http\Requests\SyncTimeEntriesRequest;
use App\Http\Requests\TimeManagementTagRequest;

class TimeManagementController extends Controller
{
    use Concerns\RespondsWithJson;

    public function getEntriesByDate($date)
    {
        $entries = TimeManagementEntry::with('tag')
            ->where('date', $date)
            ->get();

        $tags = TimeManagementTag::all();

        return $this->jsonSuccess([
            'entries' => $entries,
            'tags' => $tags
        ]);
    }

    public function syncEntries(SyncTimeEntriesRequest $request)
    {
        $validated = $request->validated();
        $date = $validated['date'];
        $entriesData = $validated['entries'] ?? [];

        // Remove existing entries for this date to sync
        TimeManagementEntry::where('date', $date)->delete();

        foreach ($entriesData as $data) {
            if (empty($data['task_name'])) continue;

            TimeManagementEntry::create([
                'date' => $date,
                'task_name' => $data['task_name'],
                'start_time' => ($data['start_time'] ?? null) ?: null,
                'end_time' => ($data['end_time'] ?? null) ?: null,
                'tag_id' => ($data['tag_id'] ?? null) ?: null,
            ]);
        }

        return $this->jsonSuccess(null, 'Entradas de tempo salvas com sucesso!');
    }

    public function storeTag(TimeManagementTagRequest $request)
    {
        $tag = TimeManagementTag::create($request->validated());

        if ($request->ajax()) {
            return $this->jsonSuccess(['tag' => $tag], 'Tag de tempo adicionada com sucesso.', 201);
        }

        return back()->with('success', 'Tag de tempo adicionada com sucesso!');
    }

    public function indexTags()
    {
        $tags = TimeManagementTag::all();
        // reusing some vars for layout compatibility if needed
        $date = now()->format('Y-m-d');
        $prev = '';
        $next = '';
        return view('time_management.tags', compact('tags', 'date', 'prev', 'next'));
    }

    public function updateTag(TimeManagementTagRequest $request, TimeManagementTag $tag)
    {
        $tag->update($request->validated());
        return back()->with('success', 'Tag de tempo atualizada com sucesso!');
    }

    public function destroyTag(TimeManagementTag $tag)
    {
        $tag->delete();
        return back()->with('success', 'Tag de tempo excluída com sucesso!');
    }
}
