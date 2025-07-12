<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();
        return view('tags.index', compact('tags'));
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
}
