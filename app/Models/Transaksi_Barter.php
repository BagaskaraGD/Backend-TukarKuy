<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi_Barter extends Model
{
    protected $table = 'transaksi_barters';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_pemohon',
        'id_pemilik',
        'id_barang_pemilik',
        'status_barter',
        'tanggal_pengajuan',
        'tanggal_selesai',
        'id_meetup_spot'
    ];
    
}
