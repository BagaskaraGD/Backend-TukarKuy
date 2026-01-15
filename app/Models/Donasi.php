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
        'status',
        'bukti_foto'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function meetupSpot()
    {
        return $this->belongsTo(Meetup_Spot::class, 'meetup_spot_id');
    }
}
