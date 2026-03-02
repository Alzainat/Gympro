<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('full_name', 255)->nullable();
            $table->enum('role', ['admin','trainer','member','receptionist'])->default('member');
            $table->text('avatar_url')->nullable();
            $table->text('bio')->nullable();

            $table->foreignId('trainer_id')->nullable()
                ->constrained('profiles')->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('user_id');
            $table->index('trainer_id');
            $table->index('role');
        });
    }

    public function down(): void {
        Schema::dropIfExists('profiles');
    }
};
