<?php

use App\Constants\Lanes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Valores fora das raias atuais, como o legado "next", tornam-se
        // "todo" para que toda tarefa permaneça acessível após a migração.
        $this->rebuildTasksTable(
            Lanes::getAllAsArray(),
            "CASE
                WHEN status = 'wating' THEN 'waiting'
                WHEN status = 'waiting' THEN 'waiting'
                WHEN status = 'done' THEN 'done'
                WHEN status = 'extra' THEN 'extra'
                ELSE 'todo'
            END"
        );
    }

    public function down(): void
    {
        $this->rebuildTasksTable(
            [Lanes::TODO, 'wating', Lanes::DONE, Lanes::EXTRA],
            "CASE WHEN status = 'waiting' THEN 'wating' ELSE status END"
        );
    }

    /**
     * SQLite cannot alter a CHECK constraint in place. Rebuild the table while
     * preserving every task column and the task IDs used by tag_task.
     *
     * @param array<string> $statuses
     */
    private function rebuildTasksTable(array $statuses, string $statusExpression): void
    {
        Schema::create('tasks_status_replacement', function (Blueprint $table) use ($statuses) {
            $table->id();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->enum('status', $statuses)->default(Lanes::TODO);
            $table->date('date');
            $table->integer('ordering')->default(0);
            $table->integer('repeat_days_left')->nullable();
            $table->timestamps();
            $table->integer('id_original')->nullable();
        });

        DB::statement("\n            INSERT INTO tasks_status_replacement\n                (id, title, notes, status, date, ordering, repeat_days_left, created_at, updated_at, id_original)\n            SELECT\n                id, title, notes, {$statusExpression}, date, ordering, repeat_days_left, created_at, updated_at, id_original\n            FROM tasks\n        ");

        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            DB::statement('DROP TABLE tasks');
            DB::statement('ALTER TABLE tasks_status_replacement RENAME TO tasks');
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
};
