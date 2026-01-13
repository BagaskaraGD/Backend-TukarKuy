<?php

// app/Http/Controllers/Api/Admin/DonasiAdminController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use App\Models\Barang;
use Illuminate\Http\Request;

class DonasiAdminController extends Controller
{
    public function index()
    {
        return Donasi::with(['user', 'barang', 'meetupSpot'])->latest()->get();
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:DISETUJUI_ADMIN,DIJADWALKAN,BERHASIL_DISALURKAN,DITOLAK'
        ]);

        $donasi = Donasi::findOrFail($id);
        $donasi->status = $request->status;
        $donasi->save();

        // Jika berhasil disalurkan, kurangi stok barang
        if ($request->status == 'BERHASIL_DISALURKAN') {
            $barang = Barang::find($donasi->barang_id);
            $barang->stok -= 1;
            $barang->save();
        }

        return response()->json([
            'message' => 'Status donasi berhasil diperbarui',
            'data' => $donasi
        ]);
    }
}
