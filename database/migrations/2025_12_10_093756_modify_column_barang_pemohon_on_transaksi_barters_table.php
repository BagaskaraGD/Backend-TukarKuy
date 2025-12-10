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
        Schema::table('transaksi_barters', function (Blueprint $table) {
            $table->renameColumn('barang_pemohon', 'id_barang_pemohon');
            $table->integer('id_barang_pemohon')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_barters', function (Blueprint $table) {
            $table->renameColumn('id_barang_pemohon', 'barang_pemohon');
            $table->string('barang_pemohon')->change();
        });
    }
};
