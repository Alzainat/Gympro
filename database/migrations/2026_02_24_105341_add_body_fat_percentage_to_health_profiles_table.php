<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->decimal('body_fat_percentage', 5, 2)->nullable()->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->dropColumn('body_fat_percentage');
        });
    }
};
