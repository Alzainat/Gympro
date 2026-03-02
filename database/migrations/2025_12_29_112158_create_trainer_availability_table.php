<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('trainer_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('profiles')->cascadeOnDelete();
            $table->enum('day_of_week', ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_available')->default(true);

            $table->index('trainer_id');
            $table->index('day_of_week');
        });
    }

    public function down(): void {
        Schema::dropIfExists('trainer_availability');
    }
};
