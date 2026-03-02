<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();

            // الأدمن اللي نفّذ العملية
            $table->foreignId('admin_profile_id')
                ->constrained('profiles')
                ->cascadeOnDelete();

            // نوع العملية
            $table->string('action'); 
            // مثال: create_user, create_payment

            // على شو العملية
            $table->string('target_type')->nullable(); 
            // User | Payment | Trainer | Admin

            $table->unsignedBigInteger('target_id')->nullable();

            // القيم قبل وبعد (للتعديل لاحقًا)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // معلومات أمنية
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};