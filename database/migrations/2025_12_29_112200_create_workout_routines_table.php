<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('workout_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('difficulty_level', 50)->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('creator_id');
            $table->index('is_public');
        });
    }

    public function down(): void {
        Schema::dropIfExists('workout_routines');
    }
};
