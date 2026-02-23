<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('member_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('routine_id')->constrained('workout_routines')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('profiles')->nullOnDelete();
            $table->date('start_date')->default(DB::raw('CURRENT_DATE'));
            $table->enum('status', ['active','completed','archived'])->default('active');

            $table->index('member_id');
            $table->index('routine_id');
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('member_routines');
    }
};
