<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_meals', function (Blueprint $table) {
            if (!Schema::hasColumn('member_meals', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }

            if (!Schema::hasColumn('member_meals', 'source')) {
                $table->string('source', 20)->default('payment')->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('member_meals', function (Blueprint $table) {
            if (Schema::hasColumn('member_meals', 'source')) {
                $table->dropColumn('source');
            }

            if (Schema::hasColumn('member_meals', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
};
