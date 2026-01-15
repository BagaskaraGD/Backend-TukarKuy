<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Donasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Barang = Barang::with('user')
            ->where('mode_transaksi', 'barter')
            ->latest()
            ->get();
        
        // Add foto_bar_url to each item
        $Barang->each(function ($item) {
            $item->foto_bar_url = $item->foto_bar_url;
        });
        
        return response()->json([
            'success' => true,
            'message' => 'List Data Barang',
            'data'    => $Barang
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_bar'        => 'required|string|max:255',
            'deskripsi_bar' => 'required|string',
            'stok_bar'       => 'required|integer|min:0',
            'foto_bar'       => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'kondisi'        => 'required|string|in:Baru,Bekas',
            'id_kategori'   => 'required|integer|exists:kategori_barangs,id',
            'mode_transaksi' => 'required|in:barter,donasi',
        ]);

        if ($request->mode_transaksi === 'donasi') {
            $request->validate([
                'tujuan_donasi' => 'required|string',
                'meetup_spot_id' => 'required|exists:meetup_spots,id',
                'jadwal' => 'required|date'
            ]);
        }

        $foto_barPath = null;
        if ($request->hasFile('foto_bar')) {
            $foto_barPath = $request->file('foto_bar')->store('Barang', 'public');
        }

        try {
            DB::beginTransaction();

            $Barang = Barang::create([
                'id_pengguna'     => $request->user()->id,
                'nama_bar'        => $request->nama_bar,
                'deskripsi_bar' => $request->deskripsi_bar,
                'foto_bar'       => $foto_barPath,
                'stok_bar'       => $request->stok_bar,
                'kondisi'        => $request->kondisi,
                'id_kategori'   => $request->id_kategori,
                'mode_transaksi' => $request->mode_transaksi
            ]);

            // Jika mode donasi, buat record donasi
            if ($request->mode_transaksi === 'donasi') {
                Donasi::create([
                    'user_id' => $request->user()->id,
                    'barang_id' => $Barang->id,
                    'tujuan_donasi' => $request->tujuan_donasi,
                    'meetup_spot_id' => $request->meetup_spot_id,
                    'jadwal' => $request->jadwal,
                    'status' => 'MENUNGGU_VERIFIKASI'
                ]);
            }

            DB::commit();

            // Add foto_bar_url to response
            $Barang->foto_bar_url = $Barang->foto_bar_url;

            return response()->json([
                'success' => true,
                'message' => 'Barang Created Successfully',
                'data'    => $Barang
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if database insertion fails
            if ($foto_barPath) {
                Storage::disk('public')->delete($foto_barPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create barang: ' . $e->getMessage(),
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Barang = Barang::with('user')->find($id);

        if (!$Barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang Not Found',
            ], 404);
        }

        // Add foto_bar_url
        $Barang->foto_bar_url = $Barang->foto_bar_url;

        return response()->json([
            'success' => true,
            'message' => 'Detail Data Barang',
            'data'    => $Barang
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $Barang = Barang::find($id);

        if (!$Barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang Not Found',
            ], 404);
        }

        // Check ownership
        if ($Barang->id_pengguna !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'nama_bar'        => 'required|string|max:255',
            'deskripsi_bar' => 'required|string',
            'foto_bar'       => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stok_bar'       => 'required|integer|min:0',
            'kondisi'        => 'required|string|in:Baru,Bekas',
            'id_kategori'   => 'required|integer|exists:kategori_barangs,id',
            'mode_transaksi' => 'required|in:barter,donasi',
        ]);

        if ($request->hasFile('foto_bar')) {
            // Delete old foto_bar
            if ($Barang->foto_bar) {
                Storage::disk('public')->delete($Barang->foto_bar);
            }
            $Barang->foto_bar = $request->file('foto_bar')->store('Barang', 'public');
        }

        $Barang->update([
            'nama_bar'        => $request->nama_bar,
            'deskripsi_bar' => $request->deskripsi_bar,
            'foto_bar'       => $Barang->foto_bar,
            'stok_bar'       => $request->stok_bar,
            'kondisi'        => $request->kondisi,
            'mode_transaksi' => $request->mode_transaksi
        ]);

        // Add foto_bar_url to response
        $Barang->foto_bar_url = $Barang->foto_bar_url;

        return response()->json([
            'success' => true,
            'message' => 'Barang Updated Successfully',
            'data'    => $Barang
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $Barang = Barang::find($id);

        if (!$Barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang Not Found',
            ], 404);
        }

        // Check ownership
        if ($Barang->id_pengguna !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($Barang->foto_bar) {
            Storage::disk('public')->delete($Barang->foto_bar);
        }

        $Barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang Deleted Successfully',
        ], 200);
    }
}
