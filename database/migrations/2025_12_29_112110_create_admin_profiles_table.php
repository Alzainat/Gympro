<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admin_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique()->constrained('profiles')->cascadeOnDelete();
            $table->enum('permission_level', ['super_admin','manager','editor'])->default('editor');
            $table->string('department', 100)->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('admin_profiles');
    }
};
