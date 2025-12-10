<?php

namespace Database\Seeders;

use Carbon\Traits\Timestamp;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetupSpotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('meetup_spots')->insert([
            'nama_tempat' => 'Universitas Dinamika',
            'alamat' => 'Jl.Raya Kedung Baruk No. 98',
            'kota' => 'Surabaya',
        ]);
    }
}
