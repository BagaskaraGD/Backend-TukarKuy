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
    
    public function barang_tawar()
    {
        return $this->hasMany(Barang_Tawar::class, 'id_transaksi', 'id');
    }

    public function barang_pemilik()
    {
        return $this->belongsTo(Barang::class, 'id_barang_pemilik');
    }

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'id_pemilik');
    }

    public function pemohon()
    {
        return $this->belongsTo(User::class, 'id_pemohon');
    }

    public function meetup_spot()
    {
        return $this->belongsTo(Meetup_Spot::class, 'id_meetup_spot');
    }
}
