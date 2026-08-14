<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Http\Requests\StoreAchievementRequest;
use App\Http\Requests\UpdateAchievementRequest;

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
    public function store(StoreAchievementRequest $request)
    {
        Achievement::create($request->validated());

        return redirect()->route('achievements.index')->with('success', 'Conquista adicionada!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAchievementRequest $request, Achievement $achievement)
    {
        // Period is NOT allowed to be changed
        $achievement->update($request->validated());

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
