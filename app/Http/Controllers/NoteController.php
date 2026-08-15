<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Http\Requests\NoteRequest;
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

    public function store(NoteRequest $request)
    {
        $note = Note::create($request->validated());

        return redirect()
            ->route('notes.show', $note)
            ->with('success', 'Anotação criada com sucesso!');
    }

    public function show(Note $note)
    {
        $contentHtml = Str::markdown($this->withoutDuplicatedFirstHeading($note), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return view('notes.show', compact('note', 'contentHtml'));
    }

    public function edit(Note $note)
    {
        return view('notes.edit', compact('note'));
    }

    public function update(NoteRequest $request, Note $note)
    {
        $note->update($request->validated());

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

    /**
     * Evita repetir o título da anotação quando o conteúdo Markdown começa com
     * um H1 igual. A descrição permanece no cabeçalho da página.
     */
    private function withoutDuplicatedFirstHeading(Note $note): string
    {
        $content = $note->content ?? '';

        $matched = preg_match(
            '/\\A(?:\\xEF\\xBB\\xBF)?(?:[ \\t]*\\R)*[ \\t]*#(?!#)[ \\t]+(?<heading>.*?)(?:[ \\t]+#+)?[ \\t]*(?:\\R|$)/',
            $content,
            $matches,
        );

        if (! $matched || Str::squish($matches['heading']) !== Str::squish($note->title)) {
            return $content;
        }

        return substr($content, strlen($matches[0]));
    }
}
