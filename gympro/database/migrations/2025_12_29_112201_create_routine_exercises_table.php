<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('routine_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained('workout_routines')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->integer('sets')->default(3);
            $table->integer('reps')->default(12);
            $table->integer('rest_seconds')->default(60);
            $table->integer('order_index')->nullable();
            $table->text('notes')->nullable();

            $table->index('routine_id');
            $table->index('exercise_id');
            $table->index('order_index');
        });
    }

    public function down(): void {
        Schema::dropIfExists('routine_exercises');
    }
};
