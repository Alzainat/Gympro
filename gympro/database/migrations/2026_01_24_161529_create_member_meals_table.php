<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_meals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('profiles')
                ->cascadeOnDelete();

            $table->foreignId('meal_id')
                ->constrained('meals')
                ->cascadeOnDelete();

            $table->foreignId('assigned_by')
                ->constrained('profiles')
                ->cascadeOnDelete();

            $table->enum('meal_time', ['breakfast','lunch','dinner','snack'])
                ->nullable();

            $table->date('start_date')->useCurrent();
            $table->boolean('is_active')->default(true);

            $table->index(['member_id', 'meal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_meals');
    }
};