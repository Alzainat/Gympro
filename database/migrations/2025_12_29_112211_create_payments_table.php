<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('profiles')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', ['membership','training_session','class_booking','product','service','other']);
            $table->enum('payment_method', ['cash','credit_card','debit_card','bank_transfer','digital_wallet'])->default('cash');
            $table->unsignedBigInteger('reference_id')->nullable(); // no FK in original
            $table->string('transaction_id', 255)->nullable();
            $table->enum('status', ['pending','completed','failed','refunded'])->default('completed');
            $table->timestamp('payment_date')->useCurrent();
            $table->text('notes')->nullable();

            $table->foreignId('processed_by')->nullable()->constrained('profiles')->nullOnDelete();

            $table->index('user_id');
            $table->index('status');
            $table->index('payment_date');
            $table->index('payment_type');
        });
    }

    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
