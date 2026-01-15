<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum DIJADWALKAN menjadi DITERIMA
        DB::statement("
            ALTER TABLE donasis 
            MODIFY COLUMN status 
            ENUM(
                'MENUNGGU_VERIFIKASI',
                'DISETUJUI_ADMIN',
                'DITERIMA',
                'BERHASIL_DISALURKAN',
                'DITOLAK'
            ) 
            DEFAULT 'MENUNGGU_VERIFIKASI'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum lama jika rollback
        DB::statement("
            ALTER TABLE donasis 
            MODIFY COLUMN status 
            ENUM(
                'MENUNGGU_VERIFIKASI',
                'DISETUJUI_ADMIN',
                'DIJADWALKAN',
                'BERHASIL_DISALURKAN',
                'DITOLAK'
            ) 
            DEFAULT 'MENUNGGU_VERIFIKASI'
        ");
    }
};
