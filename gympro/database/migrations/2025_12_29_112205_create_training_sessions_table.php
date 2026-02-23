<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->dateTime('session_date');
            $table->integer('duration_minutes')->default(60);
            $table->integer('max_participants')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('trainer_id');
            $table->index('session_date');
            $table->index('is_active');
        });
    }

    public function down(): void {
        Schema::dropIfExists('training_sessions');
    }
};