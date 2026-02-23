<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('trainer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique()->constrained('profiles')->cascadeOnDelete();
            $table->json('specializations')->nullable();
            $table->text('certification_url')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->unsignedInteger('review_count')->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->json('work_schedule')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('trainer_profiles');
    }
};