<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barangs';   

    protected $primaryKey = 'id';

    protected $fillable = [
        'nama_bar',
        'deskripsi_bar',
        'foto_bar',
        'id_pengguna',
        'stok_bar',
        'kondisi',
        'id_kategori'
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}