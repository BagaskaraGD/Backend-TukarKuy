<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meetup_Spot extends Model
{
    protected $table = 'meetup_spots';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama_tempat',
        'alamat',
        'kota'
    ];
}
