<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barangs';   

    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'nama_bar',
        'deskripsi_bar',
        'foto_bar',
        'id_pengguna',
        'created_at',
        'updated_at',
        'stok_bar',
        'kondisi'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}