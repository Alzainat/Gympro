<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('profiles')->nullOnDelete();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('target_calories')->nullable();
            $table->integer('target_protein')->nullable();
            $table->integer('target_carbs')->nullable();
            $table->integer('target_fats')->nullable();
            $table->date('start_date')->default(DB::raw('CURRENT_DATE'));
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('member_id');
            $table->index('is_active');
        });
    }

    public function down(): void {
        Schema::dropIfExists('diet_plans');
    }
};
