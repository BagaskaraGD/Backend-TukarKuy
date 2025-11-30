<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        $Kategori = Kategori::latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'List Data Kategori',
            'data'    => $Kategori
        ], 200);
    }
}
