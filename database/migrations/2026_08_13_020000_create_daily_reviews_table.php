<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reviews', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedTinyInteger('mood')->nullable();
            $table->unsignedTinyInteger('energy')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->longText('report_markdown')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reviews');
    }
};
