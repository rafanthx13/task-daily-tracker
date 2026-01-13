<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_management_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('time_management_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('task_name');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->foreignId('tag_id')->nullable()->constrained('time_management_tags')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_management_entries');
        Schema::dropIfExists('time_management_tags');
    }
};
