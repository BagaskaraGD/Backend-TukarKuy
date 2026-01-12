<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UlasanController extends Controller
{
    /**
     * Menampilkan daftar ulasan.
     * Bisa digunakan untuk mengambil semua ulasan atau filter ulasan yang DITERIMA user tertentu.
     * Contoh request: GET /api/ulasan?user_id=5
     */
    public function index(Request $request)
    {
        // Mulai query
        $query = Ulasan::query();

        // Jika ada parameter 'user_id', ambil ulasan yang DITERIMA user tersebut
        // Berguna untuk menampilkan list review di halaman profil orang lain
        if ($request->has('user_id')) {
            $query->where('id_penerima_ulasan', $request->user_id);
        }

        // Eager load data pemberi ulasan (nama & foto) agar efisien
        // Pastikan model Ulasan sudah punya relasi 'pemberi' seperti jawaban sebelumnya
        $ulasans = $query->with(['pemberi:id,name,foto_profil'])
            ->latest() // Urutkan dari yang terbaru
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ulasans,
            'message' => 'Data ulasan berhasil diambil'
        ], 200);
    }

    /**
     * Menyimpan ulasan baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            // Pastikan nama tabel transaksi sesuai (misal: 'transactions' atau 'barter_transactions')
            'id_transaksi' => 'required|integer',
            'id_penerima_ulasan' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Ambil ID User yang sedang login (Pemberi Ulasan)
        $reviewerId = Auth::id();

        // Fallback keamanan: Jika user tidak login (misal test tanpa token), return error
        if (!$reviewerId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. User harus login.'
            ], 401);
        }

        // 3. Cek Duplikasi: Pastikan user belum pernah mereview transaksi ini sebelumnya
        $existingReview = Ulasan::where('id_transaksi', $request->id_transaksi)
            ->where('id_pemberi_ulasan', $reviewerId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan ulasan untuk transaksi ini.'
            ], 409); // 409 Conflict
        }

        // 4. Simpan ke Database
        try {
            $ulasan = Ulasan::create([
                'id_transaksi' => $request->id_transaksi,
                'id_pemberi_ulasan' => $reviewerId, // Dari token
                'id_penerima_ulasan' => $request->id_penerima_ulasan,
                'rating' => $request->rating,
                'komentar' => $request->komentar,
            ]);

            return response()->json([
                'success' => true,
                'data' => $ulasan,
                'message' => 'Ulasan berhasil dikirim'
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan ulasan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu ulasan spesifik.
     */
    public function show(string $id)
    {
        $ulasan = Ulasan::with(['pemberi', 'penerima'])->find($id);

        if (!$ulasan) {
            return response()->json([
                'success' => false,
                'message' => 'Ulasan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ulasan,
            'message' => 'Detail ulasan berhasil diambil'
        ]);
    }

    /**
     * Update ulasan (Opsional, jika fitur edit ulasan diinginkan).
     */
    public function update(Request $request, string $id)
    {
        $ulasan = Ulasan::find($id);

        if (!$ulasan) {
            return response()->json(['success' => false, 'message' => 'Ulasan tidak ditemukan'], 404);
        }

        // Pastikan yang mengedit adalah pemilik ulasan
        if ($ulasan->id_pemberi_ulasan != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'komentar' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $ulasan->update($request->only(['rating', 'komentar']));

        return response()->json([
            'success' => true,
            'data' => $ulasan,
            'message' => 'Ulasan berhasil diperbarui'
        ]);
    }

    /**
     * Hapus ulasan (Opsional).
     */
    public function destroy(string $id)
    {
        $ulasan = Ulasan::find($id);

        if (!$ulasan) {
            return response()->json(['success' => false, 'message' => 'Ulasan tidak ditemukan'], 404);
        }

        // Pastikan yang menghapus adalah pemilik ulasan (atau admin)
        if ($ulasan->id_pemberi_ulasan != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ulasan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil dihapus'
        ]);
    }
}
