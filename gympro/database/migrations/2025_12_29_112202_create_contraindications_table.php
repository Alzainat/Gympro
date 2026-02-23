<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contraindications', function (Blueprint $table) {
            $table->id();
            $table->string('condition_keyword', 255)->nullable();
            $table->enum('target_type', ['exercise','meal'])->nullable();
            $table->string('blocked_keyword', 255)->nullable();
            $table->enum('match_type', ['exact','partial'])->default('partial');
            $table->text('reason')->nullable();
            $table->enum('severity_level', ['strict','warning'])->default('strict');

            $table->index('target_type');
            $table->index('match_type');
            $table->index('severity_level');
        });
    }

    public function down(): void {
        Schema::dropIfExists('contraindications');
    }
};