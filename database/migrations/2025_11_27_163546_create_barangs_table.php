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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bar');
            $table->text('deskripsi_bar');
            $table->string('foto_bar');
            $table->integer('id_pengguna');
            $table->integer('stok_bar');
            $table->string('kondisi');
            $table->integer('id_kategori');
            $table->timestamps();
        });
        Schema::create('kategori_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
        Schema::dropIfExists('kategori_barangs');
    }
};
