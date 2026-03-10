<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    /**
     * GET /api/transaksi
     * Menampilkan semua data transaksi
     */
    public function index()
    {
        $transaksi = Transaksi::with(['pelanggan', 'detailTransaksi.sepeda'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Data semua transaksi berhasil diambil',
            'data'    => $transaksi
        ], 200);
    }

    /**
     * GET /api/transaksi/{id}
     * Menampilkan detail satu transaksi berdasarkan ID
     */
    public function show($id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'detailTransaksi.sepeda'])->find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil diambil',
            'data'    => $transaksi
        ], 200);
    }

    /**
     * POST /api/transaksi
     * Membuat transaksi baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id'    => 'required|exists:pelanggans,id',
            'tgl_transaksi'   => 'required|date',
            'total_harga'     => 'required|numeric|min:0',
            'metode_bayar'    => 'required|in:tunai,transfer,kartu_kredit,dompet_digital',
            'status'          => 'required|in:pending,diproses,selesai,dibatalkan',
            'catatan'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $transaksi = Transaksi::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat',
            'data'    => $transaksi->load(['pelanggan'])
        ], 201);
    }

    /**
     * PUT/PATCH /api/transaksi/{id}
     * Mengupdate data transaksi berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'pelanggan_id'  => 'sometimes|exists:pelanggans,id',
            'tgl_transaksi' => 'sometimes|date',
            'total_harga'   => 'sometimes|numeric|min:0',
            'metode_bayar'  => 'sometimes|in:tunai,transfer,kartu_kredit,dompet_digital',
            'status'        => 'sometimes|in:pending,diproses,selesai,dibatalkan',
            'catatan'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $transaksi->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diupdate',
            'data'    => $transaksi
        ], 200);
    }

    /**
     * DELETE /api/transaksi/{id}
     * Menghapus transaksi berdasarkan ID
     */
    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        // Hapus detail transaksi terkait terlebih dahulu
        $transaksi->detailTransaksi()->delete();
        $transaksi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi beserta detailnya berhasil dihapus'
        ], 200);
    }
}