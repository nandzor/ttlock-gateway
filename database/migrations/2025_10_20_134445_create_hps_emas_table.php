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
        Schema::create('hps_emas', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_barang')->nullable();
            $table->decimal('stle_rp', 15, 2)->nullable();
            $table->integer('kadar_karat')->nullable();
            $table->decimal('berat_gram', 8, 2)->nullable();
            $table->decimal('nilai_taksiran_rp', 15, 2)->nullable();
            $table->decimal('ltv', 5, 2)->nullable();
            $table->decimal('uang_pinjaman_rp', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hps_emas');
    }
};
