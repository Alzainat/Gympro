<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique()->constrained('profiles')->cascadeOnDelete();
            $table->enum('membership_tier', ['bronze','silver','gold','platinum'])->default('bronze');
            $table->date('join_date')->default(DB::raw('CURRENT_DATE'));
            $table->timestamp('last_checkin')->nullable();
            $table->enum('status', ['active','inactive','banned'])->default('active');
        });
    }

    public function down(): void {
        Schema::dropIfExists('member_profiles');
    }
};
