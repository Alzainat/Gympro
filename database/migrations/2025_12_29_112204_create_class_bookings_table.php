<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('class_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('profiles')->cascadeOnDelete();
            $table->timestamp('booking_time')->useCurrent();
            $table->enum('status', ['booked','cancelled','attended'])->default('booked');

            $table->unique(['schedule_id', 'member_id'], 'unique_booking');
            $table->index('member_id');
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('class_bookings');
    }
};
