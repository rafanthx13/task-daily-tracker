<?php

namespace Tests\Feature;

use App\Constants\Lanes;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskCopyOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Protege os fluxos HTTP essenciais do ciclo de vida de uma tarefa.
 *
 * RefreshDatabase executa as migrations no banco SQLite em memória configurado
 * em phpunit.xml. Portanto, cada teste começa sem registros e não altera o
 * arquivo database/database.sqlite usado pela aplicação local.
 */
class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A criação por formulário deve persistir os campos da tarefa e a relação
     * muitos-para-muitos com as tags selecionadas.
     */
    public function test_creates_a_task_with_its_tags(): void
    {
        $tag = Tag::create(['name' => 'Trabalho', 'color' => '#2563eb']);

        $response = $this->post(route('tasks.store'), [
            'title' => 'Preparar planejamento',
            'notes' => 'Definir prioridades da semana.',
            'date' => '2026-08-15',
            'status' => Lanes::TODO,
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('tasks', [
            'title' => 'Preparar planejamento',
            'notes' => 'Definir prioridades da semana.',
            'status' => Lanes::TODO,
        ]);

        $task = Task::where('title', 'Preparar planejamento')->firstOrFail();
        $this->assertSame('2026-08-15', $task->date->toDateString());
        $this->assertDatabaseHas('tag_task', [
            'task_id' => $task->id,
            'tag_id' => $tag->id,
        ]);
    }

    /**
     * A edição via AJAX deve atualizar os dados e sincronizar tags: relações
     * antigas saem e somente as tags enviadas permanecem vinculadas.
     */
    public function test_updates_a_task_and_replaces_its_tags(): void
    {
        $oldTag = Tag::create(['name' => 'Antiga']);
        $newTag = Tag::create(['name' => 'Importante']);
        $task = Task::create([
            'title' => 'Título original',
            'notes' => 'Nota original',
            'date' => '2026-08-15',
            'status' => Lanes::TODO,
        ]);
        $task->tags()->attach($oldTag);

        $response = $this->putJson("/tasks/{$task->id}", [
            'title' => 'Título atualizado',
            'notes' => 'Nota atualizada',
            'tag_ids' => [$newTag->id],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.task.title', 'Título atualizado');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título atualizado',
            'notes' => 'Nota atualizada',
        ]);
        $this->assertDatabaseHas('tag_task', ['task_id' => $task->id, 'tag_id' => $newTag->id]);
        $this->assertDatabaseMissing('tag_task', ['task_id' => $task->id, 'tag_id' => $oldTag->id]);
    }

    /**
     * A exclusão via AJAX deve remover a tarefa identificada pelo endpoint.
     */
    public function test_deletes_a_task(): void
    {
        $task = Task::create([
            'title' => 'Tarefa a excluir',
            'date' => '2026-08-15',
            'status' => Lanes::TODO,
        ]);

        $response = $this->deleteJson("/tasks/{$task->id}");

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * O drag and drop da interface usa este endpoint; o teste assegura que a
     * nova raia válida é gravada e devolvida no contrato JSON padronizado.
     */
    public function test_moves_a_task_to_another_lane(): void
    {
        $task = Task::create([
            'title' => 'Aguardando revisão',
            'date' => '2026-08-15',
            'status' => Lanes::TODO,
        ]);

        $response = $this->putJson(route('tasks.change-lane', $task), [
            'status' => Lanes::WAITING,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.task.status', Lanes::WAITING);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => Lanes::WAITING]);
    }

    public function test_copies_pending_tasks_from_a_previous_day_only_once_per_destination_date(): void
    {
        Task::create([
            'title' => 'Tarefa pendente',
            'date' => '2026-08-14',
            'status' => Lanes::TODO,
        ]);

        $firstResponse = $this->getJson(route('tasks.copyTasksFromDate', [
            'oldDate' => '2026-08-14',
            'todayDate' => '2026-08-15',
        ]));

        $firstResponse->assertOk()->assertJsonPath('success', true);
        $operation = TaskCopyOperation::firstOrFail();
        $this->assertSame('2026-08-14', $operation->source_date->toDateString());
        $this->assertSame('2026-08-15', $operation->destination_date->toDateString());
        $this->assertSame(1, Task::whereDate('date', '2026-08-15')->count());

        $secondResponse = $this->getJson(route('tasks.copyTasksFromDate', [
            'oldDate' => '2026-08-14',
            'todayDate' => '2026-08-15',
        ]));

        $secondResponse
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'As tarefas do dia anterior já foram carregadas para esta data.');
        $this->assertSame(1, Task::whereDate('date', '2026-08-15')->count());
        $this->assertSame(1, TaskCopyOperation::count());
    }
}
