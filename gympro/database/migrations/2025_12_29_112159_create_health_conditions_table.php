<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('health_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('profiles')->cascadeOnDelete();
            $table->enum('type', ['allergy','injury','condition']);
            $table->string('name', 255)->nullable();
            $table->enum('severity', ['low','medium','high'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('detected_at')->useCurrent();

            $table->index('user_id');
            $table->index('type');
            $table->index('severity');
        });
    }

    public function down(): void {
        Schema::dropIfExists('health_conditions');
    }
};
