<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Barang = Barang::with('user')->latest()->get();
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
            'nama_bar'        => 'required',
            'deskripsi_bar' => 'required|string',
            'foto_bar'       => 'foto_bar|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'created_at',
            'updated_at',
            'stok_bar'       => 'required',
            'kondisi'        => 'required'
        ]);

        $foto_barPath = null;
        if ($request->hasFile('foto_bar')) {
            $foto_barPath = $request->file('foto_bar')->store('Barang', 'public');
        }

        $Barang = Barang::create([
            'id_pengguna'     => $request->user()->id,
            'nama_bar'        => $request->nama_bar,
            'deskripsi_bar' => $request->deskripsi_bar,
            'foto_bar'       => $foto_barPath,
            'stok_bar'       => $request->stok_bar,
            'created_at'       => $request->created_at,
            'updated_at'       => $request->updated_at,
            'kondisi'        => $request->kondisi

        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang Created Successfully',
            'data'    => $Barang
        ], 201);
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
            'nama_bar'        => 'required',
            'deskripsi_bar' => 'required|string',
            'foto_bar'       => 'foto_bar|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'created_at',
            'updated_at',
            'stok_bar'       => 'required',
            'kondisi'        => 'required'
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
            'created_at'       => $request->created_at,
            'updated_at'       => $request->updated_at,
            'stok_bar'       => $request->stok_bar,
            'kondisi'        => $request->kondisi
        ]);

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
