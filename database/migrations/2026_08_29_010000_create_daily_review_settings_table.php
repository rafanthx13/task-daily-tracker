<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_review_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('show_extra_lane')->default(true);
            $table->boolean('show_reminders')->default(false);
            $table->boolean('show_mood_energy')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_review_settings');
    }
};
