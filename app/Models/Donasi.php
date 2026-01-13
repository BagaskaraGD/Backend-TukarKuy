<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donasi extends Model
{
    protected $table = 'donasis';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'barang_id',
        'meetup_spot_id',
        'tujuan_donasi',
        'jadwal',
        'status'
    ];
}
