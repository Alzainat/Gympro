<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_routines', function (Blueprint $table) {
            if (!Schema::hasColumn('member_routines', 'source')) {
                $table->string('source', 20)
                      ->default('trainer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('member_routines', function (Blueprint $table) {
            if (Schema::hasColumn('member_routines', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
