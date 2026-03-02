<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('target_muscle', 255)->nullable();
            $table->string('equipment', 255)->nullable();
            $table->enum('difficulty', ['beginner','intermediate','advanced'])->nullable();
            $table->text('video_url')->nullable();
            $table->text('image_url')->nullable();

            $table->index('name');
            $table->index('difficulty');
        });
    }

    public function down(): void {
        Schema::dropIfExists('exercises');
    }
};
