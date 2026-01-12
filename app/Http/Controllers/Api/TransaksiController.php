<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi_Barter;
use App\Models\Barang_Tawar;
use App\Models\Barang;
use App\Models\User;
use App\Models\Meetup_Spot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Symfony\Contracts\Service\Attribute\Required;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json([
                    'message' => 'Token tidak ditemukan, user belum login.',
                    'error' => 'Unauthorized'
                ], 401);
            }

            $user = $request->user();
            $userId = $user->id;
            $isAdmin = $user->email === 'admin@gmail.com';

            $query = Transaksi_Barter::with([
                'barang_pemilik',
                'meetup_spot',
                'pemilik:id,name,email,no_wa,alamat',
                'pemohon:id,name,email,no_wa,alamat',
                'barang_tawar.barang'
            ])->orderBy('created_at', 'desc');

            // Jika bukan admin, batasi transaksi milik user
            if (!$isAdmin) {
                $query->where(function ($q) use ($userId) {
                    $q->where('id_pemilik', $userId)
                        ->orWhere('id_pemohon', $userId);
                });
            }

            // Filter status jika ada
            if ($request->filled('status')) {
                $query->where('status_barter', $request->status);
            }

            $transaksi = $query->get();

            $transformedData = $transaksi->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_pemilik' => $item->id_pemilik,
                    'id_barang_pemilik' => $item->id_barang_pemilik,
                    'id_meetup_spot' => $item->id_meetup_spot,
                    'tgl_barter' => $item->tgl_barter,
                    'status' => $item->status_barter,
                    'created_at' => $item->created_at->toISOString(),
                    'updated_at' => $item->updated_at->toISOString(),
                    'barang_pemilik' => $item->barang_pemilik,
                    'meetup_spot' => $item->meetup_spot,
                    'pemilik' => $item->pemilik,
                    'barang_tawar' => $item->barang_tawar->map(function ($tawar) {
                        return [
                            'id' => $tawar->id,
                            'id_transaksi' => $tawar->id_transaksi,
                            'id_barang' => $tawar->id_barang,
                            'barang_id' => $tawar->id_barang,
                            'qty' => $tawar->qty,
                            'quantity' => $tawar->qty,
                            'created_at' => $tawar->created_at->toISOString(),
                            'updated_at' => $tawar->updated_at->toISOString(),
                            'barang' => $tawar->barang
                        ];
                    })
                ];
            });

            return response()->json([
                'message' => 'Success',
                'data' => $transformedData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch transactions',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pemilik' => 'required|exists:users,id',
            'id_barang_pemilik' => 'required|exists:barangs,id',
            'id_meetup_spot' => 'required|exists:meetup_spots,id',
            'tgl_barter' => 'required',
            'barang_tawar' => 'required|array|min:1',
            'barang_tawar.*' => 'exists:barangs,id',
            'qty' => 'required|array|min:1',
            'qty.*' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi jumlah barang dan qty harus sama
        if (count($request->barang_tawar) !== count($request->qty)) {
            return response()->json([
                'status' => false,
                'message' => 'Jumlah barang dan qty tidak sesuai'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Validasi setiap barang tawar
            foreach ($request->barang_tawar as $index => $idBarang) {
                $barang = Barang::find($idBarang);

                // Cek kepemilikan barang
                if ($barang->id_pengguna !== $request->user()->id) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Barang ' . $barang->nama_bar . ' bukan milik Anda'
                    ], 422);
                }

                // Cek stok tersedia
                if ($request->qty[$index] > $barang->stok_bar) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Stok ' . $barang->nama_bar . ' tidak mencukupi. Tersedia: ' . $barang->stok_bar . ', Ditawarkan: ' . $request->qty[$index]
                    ], 422);
                }
            }

            // 1. Buat Transaksi Barter
            $transaksi = Transaksi_Barter::create([
                'id_pemohon' => $request->user()->id,
                'id_pemilik' => $request->id_pemilik,
                'id_barang_pemilik' => $request->id_barang_pemilik,
                'status_barter' => 'menunggu_konfirmasi',
                'tanggal_pengajuan' => now(),
                'tgl_barter' => $request->tgl_barter,
                'id_meetup_spot' => $request->id_meetup_spot,
            ]);

            // 2. Simpan Barang Tawar
            foreach ($request->barang_tawar as $index => $idBarang) {
                Barang_Tawar::create([
                    'id_transaksi' => $transaksi->id,
                    'id_barang' => $idBarang,
                    'qty' => $request->qty[$index]
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Transaksi berhasil diajukan',
                'data' => $transaksi->load('barang_tawar.barang')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // Validasi user login
            if (!request()->user()) {
                return response()->json([
                    'message' => 'Token tidak ditemukan, user belum login.',
                    'error' => 'Unauthorized'
                ], 401);
            }
            $userId = request()->user()->id;
            // Fetch transaction dengan eager loading
            $transaksi = Transaksi_Barter::with([
                'barang_pemilik',
                'meetup_spot',
                'pemilik:id,name,email,no_wa,alamat',
                'pemohon:id,name,email,no_wa,alamat',
                'barang_tawar.barang'
            ])->where(function ($query) use ($userId) {
                $query->where('id_pemilik', $userId)
                    ->orWhere('id_pemohon', $userId);
            })
                ->find($id);
            if (!$transaksi) {
                return response()->json([
                    'message' => 'Transaksi tidak ditemukan',
                    'error' => 'Not Found'
                ], 404);
            }
            // Transform data untuk match format yang Flutter harapkan
            $transformedData = [
                'id' => $transaksi->id,
                'id_pemilik' => $transaksi->id_pemilik,
                'id_barang_pemilik' => $transaksi->id_barang_pemilik,
                'id_meetup_spot' => $transaksi->id_meetup_spot,
                'status' => $transaksi->status_barter,
                'created_at' => $transaksi->created_at->toISOString(),
                'updated_at' => $transaksi->updated_at->toISOString(),
                'barang_pemilik' => $transaksi->barang_pemilik,
                'tgl_barter' => $transaksi->tgl_barter,
                'meetup_spot' => $transaksi->meetup_spot,
                'pemilik' => $transaksi->pemilik,
                'pemohon' => $transaksi->pemohon,  // Ini pembeli!
                'barang_tawar' => $transaksi->barang_tawar->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'id_transaksi' => $item->id_transaksi,
                        'id_barang' => $item->id_barang,
                        'barang_id' => $item->id_barang,
                        'qty' => $item->qty,
                        'quantity' => $item->qty,
                        'created_at' => $item->created_at->toISOString(),
                        'updated_at' => $item->updated_at->toISOString(),
                        'barang' => $item->barang
                    ];
                })
            ];
            return response()->json([
                'message' => 'Success',
                'data' => $transformedData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch transaction',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $transaksi = Transaksi_Barter::find($id);

            if (!$transaksi) {
                return response()->json([
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            $validated = $request->validate([
                'status' => 'required|string|in:menunggu_konfirmasi,disepakati,berhasil,gagal,ditolak,batal'
            ]);
            $transaksi->update([
                'status_barter' => $validated['status']
            ]);
            return response()->json([
                'message' => 'Status berhasil diperbarui',
                'data' => $transaksi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // --- METHOD BARU: VERIFIKASI AKHIR (UPLOAD FOTO + STATUS) ---
    public function verifikasiAkhir(Request $request, $id)
    {
        try {
            $transaksi = Transaksi_Barter::find($id);

            if (!$transaksi) {
                return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
            }

            // Validasi Input
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:selesai,gagal_transaksi',
                // Bukti transaksi wajib ada jika status selesai, boleh null jika gagal
                'bukti_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'keterangan' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle Upload Foto
            if ($request->hasFile('bukti_transaksi')) {
                // Hapus foto lama jika ada (opsional)
                if ($transaksi->bukti_transaksi && Storage::disk('public')->exists($transaksi->bukti_transaksi)) {
                    Storage::disk('public')->delete($transaksi->bukti_transaksi);
                }

                $file = $request->file('bukti_transaksi');
                $path = $file->store('bukti_transaksi', 'public'); // Simpan di storage/app/public/bukti_transaksi
                $transaksi->bukti_transaksi = $path;
            }

            // Update Status dan Keterangan
            $transaksi->status_barter = $request->status;

            if ($request->filled('keterangan')) {
                $transaksi->keterangan = $request->keterangan;
            }

            // Set Tanggal Selesai
            $transaksi->tanggal_selesai = now();

            $transaksi->save();

            return response()->json([
                'status' => true,
                'message' => 'Verifikasi akhir berhasil disimpan',
                'data' => $transaksi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        //
    }
}
