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
        Schema::create('hps_elektronik', function (Blueprint $table) {
            $table->id();
            $table->string('kdwilayah')->nullable();
            $table->string('jenis_barang')->nullable();
            $table->string('merek')->nullable();
            $table->string('barang')->nullable();
            $table->integer('tahun')->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->string('grade')->nullable();
            $table->text('kondisi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hps_elektronik');
    }
};
