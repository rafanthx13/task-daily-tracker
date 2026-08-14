<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Http\Requests\TaskTagRequest;
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

    public function store(TaskTagRequest $request)
    {
        Tag::create($request->validated());
        return back()->with('success', 'Tag adicionada com sucesso!');
    }

    public function update(TaskTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());
        return back()->with('success', 'Tag atualizada com sucesso!');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return back()->with('success', 'Tag excluída com sucesso!');
    }
}
