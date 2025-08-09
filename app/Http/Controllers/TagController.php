<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TagController extends Controller
{
    public function index($date = null)
    {
        $date = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();

        // previous working day (for display)
        $prev = $this->previousWorkingDay($date->copy());
        $next = $this->nextWorkingDay($date->copy());

        $tags = Tag::all();
        return view('tags.index', compact('tags', 'date'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Tag::create($request->only('name'));
        return back()->with('success', 'Tag adicionada com sucesso!');
    }

    public function update(Request $request, Tag $tag)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $tag->update($request->only('name'));
        return back()->with('success', 'Tag atualizada com sucesso!');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return back()->with('success', 'Tag excluída com sucesso!');
    }

    private function previousWorkingDay(Carbon $d)
    {
        $d->subDay();
        while ($d->isWeekend()) $d->subDay();
        return $d;
    }

    private function nextWorkingDay(Carbon $d)
    {
        $d->addDay();
        while ($d->isWeekend()) $d->addDay();
        return $d;
    }
}
