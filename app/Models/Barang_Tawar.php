<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang_Tawar extends Model
{
    protected $table = 'transaksi_barang_tawars';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'id_transaksi',
        'id_barang',
        'qty'
    ];
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
