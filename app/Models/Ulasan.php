<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional: jika pakai factory
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasFactory;

    protected $table = 'ulasans';
    protected $primaryKey = 'id';

    // Pastikan timestamp aktif karena ada kolom created_at & updated_at di schema
    public $timestamps = true;

    protected $fillable = [
        'id_transaksi',
        'komentar',
        'rating',
        'id_pemberi_ulasan',
        'id_penerima_ulasan'
    ];

    /**
     * Casting tipe data untuk memastikan output JSON sesuai (misal integer tidak jadi string).
     */
    protected $casts = [
        'id_transaksi' => 'integer',
        'rating' => 'integer',
        'id_pemberi_ulasan' => 'integer',
        'id_penerima_ulasan' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELASI (RELATIONSHIPS)
    |--------------------------------------------------------------------------
    | Diasumsikan nama model lain adalah 'Transaksi' dan 'User'.
    | Sesuaikan dengan nama class model Anda yang sebenarnya.
    */

    /**
     * Relasi ke model Transaksi.
     * Ulasan milik satu transaksi.
     */
    public function transaksi()
    {
        // Sesuaikan 'Transaksi::class' dengan nama model transaksi Anda (misal: BarterTransaction::class)
        return $this->belongsTo(Transaksi_Barter::class, 'id_transaksi', 'id');
    }

    /**
     * Relasi ke model User sebagai Pemberi Ulasan.
     */
    public function pemberi()
    {
        return $this->belongsTo(User::class, 'id_pemberi_ulasan', 'id');
    }

    /**
     * Relasi ke model User sebagai Penerima Ulasan.
     */
    public function penerima()
    {
        return $this->belongsTo(User::class, 'id_penerima_ulasan', 'id');
    }
}
