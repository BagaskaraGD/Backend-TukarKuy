<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function getFotoBarUrlAttribute()
    {
        if (!$this->foto_bar) {
            return null;
        }
        
        $baseUrl = config('app.url');
        $storageUrl = Storage::url($this->foto_bar);
        
        // Ensure we don't double-prepend the base URL
        if (str_starts_with($storageUrl, 'http')) {
            return $storageUrl;
        }
        
        return rtrim($baseUrl, '/') . $storageUrl;
    }
}