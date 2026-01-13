<?php

namespace App\Http\Controllers;

use App\Models\TimeManagementEntry;
use App\Models\TimeManagementTag;
use Illuminate\Http\Request;

class TimeManagementController extends Controller
{
    public function getEntriesByDate($date)
    {
        $entries = TimeManagementEntry::with('tag')
            ->where('date', $date)
            ->get();

        $tags = TimeManagementTag::all();

        return response()->json([
            'entries' => $entries,
            'tags' => $tags
        ]);
    }

    public function syncEntries(Request $request)
    {
        $date = $request->input('date');
        $entriesData = $request->input('entries', []);

        // Remove existing entries for this date to sync
        TimeManagementEntry::where('date', $date)->delete();

        foreach ($entriesData as $data) {
            if (empty($data['task_name'])) continue;

            TimeManagementEntry::create([
                'date' => $date,
                'task_name' => $data['task_name'],
                'start_time' => $data['start_time'] ?: null,
                'end_time' => $data['end_time'] ?: null,
                'tag_id' => $data['tag_id'] ?: null,
            ]);
        }

        return response()->json(['message' => 'Entradas de tempo salvas com sucesso!']);
    }

    public function storeTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $tag = TimeManagementTag::create($request->only('name', 'color'));

        if ($request->ajax()) {
            return response()->json($tag);
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

    public function updateTag(Request $request, TimeManagementTag $tag)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7'
        ]);
        $tag->update($request->only('name', 'color'));
        return back()->with('success', 'Tag de tempo atualizada com sucesso!');
    }

    public function destroyTag(TimeManagementTag $tag)
    {
        $tag->delete();
        return back()->with('success', 'Tag de tempo excluída com sucesso!');
    }
}
