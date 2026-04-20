<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_exercise_log_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_exercise_log_id')
                ->constrained('member_exercise_logs')
                ->cascadeOnDelete();

            $table->unsignedInteger('round');
            $table->decimal('weight', 8, 2)->nullable();
            $table->unsignedInteger('reps')->nullable();
            $table->boolean('done')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_exercise_log_sets');
    }
};
