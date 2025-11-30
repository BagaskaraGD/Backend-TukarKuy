<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori_barangs';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'nama_kategori'
    ];

}
