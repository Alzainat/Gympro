<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->date('membership_expires_at')->nullable()->after('membership_tier');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn('membership_expires_at');
        });
    }
};
