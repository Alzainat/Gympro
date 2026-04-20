<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('member_exercise_logs', function (Blueprint $table) {
            $table->dropColumn(['weight', 'sets_done', 'reps_done']);
        });
    }

    public function down(): void
    {
        Schema::table('member_exercise_logs', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->nullable();
            $table->unsignedInteger('sets_done')->nullable();
            $table->unsignedInteger('reps_done')->nullable();
        });
    }
};
