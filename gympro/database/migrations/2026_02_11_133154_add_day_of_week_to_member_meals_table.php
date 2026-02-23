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
    Schema::table('member_meals', function (Blueprint $table) {
        $table->enum('day_of_week', [
            'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'
        ])->default('Monday')->after('meal_time');

        $table->index(['member_id', 'day_of_week', 'meal_time']);
    });
}

public function down(): void
{
    Schema::table('member_meals', function (Blueprint $table) {
        $table->dropIndex(['member_id', 'day_of_week', 'meal_time']);
        $table->dropColumn('day_of_week');
    });
}
};
