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

        $prev = '';
        $next = '';

        $tags = Tag::all();
        return view('tags', compact('tags', 'date', 'prev', 'next'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string'
        ]);
        Tag::create($request->only('name', 'color'));
        return back()->with('success', 'Tag adicionada com sucesso!');
    }

    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string'
        ]);
        $tag->update($request->only('name', 'color'));
        return back()->with('success', 'Tag atualizada com sucesso!');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return back()->with('success', 'Tag excluída com sucesso!');
    }
}
