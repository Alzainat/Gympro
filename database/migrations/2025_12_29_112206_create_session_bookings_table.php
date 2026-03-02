<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('session_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('training_sessions')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('profiles')->cascadeOnDelete();
            $table->enum('status', ['booked','attended','cancelled'])->default('booked');
            $table->timestamp('booked_at')->useCurrent();

            $table->unique(['session_id', 'member_id'], 'unique_session_booking');
            $table->index('member_id');
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('session_bookings');
    }
};
