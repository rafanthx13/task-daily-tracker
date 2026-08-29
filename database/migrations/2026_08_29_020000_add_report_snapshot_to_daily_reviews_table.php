<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reviews', function (Blueprint $table) {
            $table->json('report_snapshot')->nullable()->after('report_markdown');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reviews', function (Blueprint $table) {
            $table->dropColumn('report_snapshot');
        });
    }
};
