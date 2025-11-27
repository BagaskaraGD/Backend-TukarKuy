<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    protected $table = 'ulasans';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_transaksi',
        'komentar',
        'rating',
        'id_pemberi_ulasan',
        'id_penerima_ulasan'
    ];
}
