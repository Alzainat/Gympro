<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('class_name', 255);
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('capacity')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('trainer_id');
            $table->index('start_time');
            $table->index('is_active');
        });
    }

    public function down(): void {
        Schema::dropIfExists('class_schedules');
    }
};
