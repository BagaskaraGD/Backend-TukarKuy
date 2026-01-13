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
            // Menambahkan kolom yang sudah ada di DB tapi belum di kodingan
            // 'after' digunakan agar posisi kolom rapi (opsional)

            if (!Schema::hasColumn('transaksi_barters', 'tgl_barter')) {
                $table->timestamp('tgl_barter')->nullable()->after('id_meetup_spot');
            }

            if (!Schema::hasColumn('transaksi_barters', 'bukti_transaksi')) {
                $table->string('bukti_transaksi', 100)->nullable()->after('tgl_barter');
            }

            if (!Schema::hasColumn('transaksi_barters', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('bukti_transaksi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_barters', function (Blueprint $table) {
            $table->dropColumn(['tgl_barter', 'bukti_transaksi', 'keterangan']);
        });
    }
};
