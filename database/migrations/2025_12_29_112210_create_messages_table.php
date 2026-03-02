<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('profiles')->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->boolean('is_read')->default(false);

            $table->index('sender_id');
            $table->index('receiver_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('messages');
    }
};
