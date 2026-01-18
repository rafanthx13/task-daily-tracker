<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all achievements sorted by period/ID
        // Grouping them by period for the view
        $groupedAchievements = Achievement::orderBy('id', 'desc')->get()->groupBy('period');
        return view('achievements.index', compact('groupedAchievements'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period' => 'required|string|regex:/^\d{2}\/\d{4}$/', // 01/2026
        ]);

        Achievement::create($request->all());

        return redirect()->route('achievements.index')->with('success', 'Conquista adicionada!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Achievement $achievement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Period is NOT allowed to be changed
        $achievement->update($request->only(['title', 'description']));

        return redirect()->route('achievements.index')->with('success', 'Conquista atualizada!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Achievement $achievement)
    {
        $achievement->delete();
        return redirect()->route('achievements.index')->with('success', 'Conquista removida!');
    }
}
