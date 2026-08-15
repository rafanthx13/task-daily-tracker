<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Garante que a página de uma anotação não duplique visualmente seu título.
 */
class NoteDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_hides_a_matching_first_markdown_heading_but_keeps_the_description(): void
    {
        $note = Note::create([
            'title' => 'Planejamento semanal',
            'description' => 'Definição das prioridades da próxima semana.',
            'content' => "# Planejamento semanal\n\n## Prioridades\n\n- Organizar entregas",
        ]);

        $response = $this->get(route('notes.show', $note));

        $response->assertOk()->assertSee($note->description);
        $this->assertStringNotContainsString('<h1>Planejamento semanal</h1>', $response->getContent());
        $this->assertStringContainsString('<h2>Prioridades</h2>', $response->getContent());
    }

    public function test_keeps_a_first_markdown_heading_that_differs_from_the_note_title(): void
    {
        $note = Note::create([
            'title' => 'Planejamento semanal',
            'content' => "# Objetivos\n\nConteúdo da anotação.",
        ]);

        $response = $this->get(route('notes.show', $note));

        $response->assertOk();
        $this->assertStringContainsString('<h1>Objetivos</h1>', $response->getContent());
    }
}
