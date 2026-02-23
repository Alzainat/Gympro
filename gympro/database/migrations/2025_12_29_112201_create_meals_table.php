<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('calories')->nullable();
            $table->decimal('protein', 5, 2)->nullable();
            $table->decimal('carbs', 5, 2)->nullable();
            $table->decimal('fats', 5, 2)->nullable();
            $table->json('ingredients')->nullable();
            $table->text('image_url')->nullable();

            $table->index('name');
            $table->index('calories');
            $table->foreignId('created_by')
            ->constrained('profiles')
            ->cascadeOnDelete();

            $table->enum('category', [
    'bulking',
    'weight_gain',
    'healthy',
    'cutting'
])->default('healthy');

$table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('meals');
    }
};