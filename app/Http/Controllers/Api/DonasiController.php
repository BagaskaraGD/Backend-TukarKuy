<?php

// app/Http/Controllers/Api/DonasiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonasiController extends Controller
{
    public function ajukanDonasi(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'tujuan_donasi' => 'required|string',
            'meetup_spot_id' => 'required|exists:meetup_spots,id',
            'jadwal' => 'required|date'
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        if ($barang->stok < 1) {
            return response()->json([
                'message' => 'Stok barang tidak tersedia'
            ], 400);
        }

        $donasi = Donasi::create([
            'user_id' => Auth::id(),
            'barang_id' => $barang->id,
            'tujuan_donasi' => $request->tujuan_donasi,
            'meetup_spot_id' => $request->meetup_spot_id,
            'jadwal' => $request->jadwal,
            'status' => 'MENUNGGU_VERIFIKASI'
        ]);

        return response()->json([
            'message' => 'Donasi berhasil diajukan',
            'data' => $donasi
        ]);
    }

    public function riwayatDonasi()
    {
        $donasi = Donasi::with(['barang', 'meetupSpot'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($donasi);
    }
}
