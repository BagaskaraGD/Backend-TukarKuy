<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('donasis', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('barang_id');
            $table->integer('meetup_spot_id');
            $table->string('tujuan_donasi');
            $table->dateTime('jadwal');
            $table->enum('status', [
                'MENUNGGU_VERIFIKASI',
                'DISETUJUI_ADMIN',
                'DIJADWALKAN',
                'BERHASIL_DISALURKAN',
                'DITOLAK'
            ])->default('MENUNGGU_VERIFIKASI');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donasis');
    }
};
