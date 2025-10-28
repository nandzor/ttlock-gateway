<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hps_emas', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('uang_pinjaman_rp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hps_emas', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
