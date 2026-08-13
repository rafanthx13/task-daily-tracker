<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::latest('updated_at')->get();

        return view('notes.index', compact('notes'));
    }

    public function create()
    {
        return view('notes.create');
    }

    public function store(Request $request)
    {
        $note = Note::create($this->validateNote($request));

        return redirect()
            ->route('notes.show', $note)
            ->with('success', 'Anotação criada com sucesso!');
    }

    public function show(Note $note)
    {
        $contentHtml = Str::markdown($note->content ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return view('notes.show', compact('note', 'contentHtml'));
    }

    public function edit(Note $note)
    {
        return view('notes.edit', compact('note'));
    }

    public function update(Request $request, Note $note)
    {
        $note->update($this->validateNote($request));

        return redirect()
            ->route('notes.show', $note)
            ->with('success', 'Anotação atualizada com sucesso!');
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return redirect()
            ->route('notes.index')
            ->with('success', 'Anotação excluída com sucesso!');
    }

    private function validateNote(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
        ]);
    }
}
