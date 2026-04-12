<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_meals', function (Blueprint $table) {
            $table->unsignedInteger('grams')->nullable()->after('meal_time');
        });
    }

    public function down(): void
    {
        Schema::table('member_meals', function (Blueprint $table) {
            $table->dropColumn('grams');
        });
    }
};
