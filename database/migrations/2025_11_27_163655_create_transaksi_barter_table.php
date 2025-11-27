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
        Schema::create('transaksi_barters', function (Blueprint $table) {
            $table->id();
            $table->integer('id_pemohon');
            $table->integer('id_pemilik');
            $table->integer('id_barang_pemilik');
            $table->string('barang_pemohon');
            $table->string('status_barter');
            $table->timestamp('tanggal_pengajuan');
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_barters');
    }
};
