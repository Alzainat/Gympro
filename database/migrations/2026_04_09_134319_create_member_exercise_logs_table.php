<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_exercise_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('profiles')
                ->cascadeOnDelete();

            $table->foreignId('routine_id')
                ->constrained('workout_routines')
                ->cascadeOnDelete();

            $table->foreignId('routine_exercise_id')
                ->constrained('routine_exercises')
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            $table->date('workout_date');
            $table->enum('day_of_week', [
                'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'
            ]);

            $table->decimal('weight', 8, 2)->nullable();
            $table->integer('sets_done')->nullable();
            $table->integer('reps_done')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['member_id', 'workout_date']);
            $table->index(['member_id', 'routine_exercise_id', 'workout_date'], 'member_ex_log_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_exercise_logs');
    }
};
