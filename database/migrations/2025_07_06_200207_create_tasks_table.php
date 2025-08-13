<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\Lanes;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->enum('status', Lanes::getAllAsArray())->default(Lanes::TODO);
            $table->date('date'); // a data do dia a que a task pertence
            $table->integer('ordering')->default(0);
            $table->integer('repeat_days_left')->nullable(); // se >0, será copiada nos próximos imports e decrementada
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('tag_task', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['task_id','tag_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tag_task');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('tasks');
    }
};
