<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_progress_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('profiles')
                ->cascadeOnDelete();

            $table->string('image_path');
            $table->enum('photo_type', ['baseline', 'progress', 'comparison'])->default('progress');
            $table->enum('pose', ['front', 'side', 'back'])->nullable();
            $table->text('notes')->nullable();
            $table->date('taken_at')->nullable();

            $table->timestamps();

            $table->index('member_id');
            $table->index('photo_type');
            $table->index('pose');
            $table->index('taken_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_progress_photos');
    }
};
