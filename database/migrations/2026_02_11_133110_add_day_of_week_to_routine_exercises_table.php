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
    Schema::table('routine_exercises', function (Blueprint $table) {
        $table->enum('day_of_week', [
            'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'
        ])->default('Monday')->after('exercise_id');

        $table->index(['routine_id', 'day_of_week']);
    });
}

public function down(): void
{
    Schema::table('routine_exercises', function (Blueprint $table) {
        $table->dropIndex(['routine_id', 'day_of_week']);
        $table->dropColumn('day_of_week');
    });
}
};
