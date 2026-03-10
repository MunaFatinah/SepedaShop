<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Sepeda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailTransaksiController extends Controller
{
    /**
     * GET /api/detail-transaksi
     * Menampilkan semua detail transaksi
     */
    public function index()
    {
        $detail = DetailTransaksi::with(['transaksi', 'sepeda'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Data semua detail transaksi berhasil diambil',
            'data'    => $detail
        ], 200);
    }

    /**
     * GET /api/detail-transaksi/{id}
     * Menampilkan satu detail transaksi berdasarkan ID
     */
    public function show($id)
    {
        $detail = DetailTransaksi::with(['transaksi.pelanggan', 'sepeda'])->find($id);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail transaksi dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data detail transaksi berhasil diambil',
            'data'    => $detail
        ], 200);
    }

    /**
     * POST /api/detail-transaksi
     * Menambahkan item ke dalam transaksi
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaksi_id' => 'required|exists:transaksis,id',
            'sepeda_id'    => 'required|exists:sepedas,id',
            'jumlah'       => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Cek stok sepeda
        $sepeda = Sepeda::find($request->sepeda_id);
        if ($sepeda->stok < $request->jumlah) {
            return response()->json([
                'success' => false,
                'message' => 'Stok sepeda tidak mencukupi. Stok tersedia: ' . $sepeda->stok
            ], 400);
        }

        // Hitung subtotal otomatis
        $data = $request->all();
        $data['subtotal'] = $request->jumlah * $request->harga_satuan;

        $detail = DetailTransaksi::create($data);

        // Kurangi stok sepeda
        $sepeda->decrement('stok', $request->jumlah);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan ke transaksi',
            'data'    => $detail->load(['sepeda'])
        ], 201);
    }

    /**
     * PUT/PATCH /api/detail-transaksi/{id}
     * Mengupdate item dalam transaksi
     */
    public function update(Request $request, $id)
    {
        $detail = DetailTransaksi::find($id);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail transaksi dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'jumlah'       => 'sometimes|integer|min:1',
            'harga_satuan' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Kembalikan stok lama sebelum update
        $selisih = ($request->jumlah ?? $detail->jumlah) - $detail->jumlah;
        $sepeda  = Sepeda::find($detail->sepeda_id);

        if ($selisih > 0 && $sepeda->stok < $selisih) {
            return response()->json([
                'success' => false,
                'message' => 'Stok sepeda tidak mencukupi untuk penambahan jumlah. Stok tersedia: ' . $sepeda->stok
            ], 400);
        }

        // Update subtotal otomatis
        $jumlah      = $request->jumlah       ?? $detail->jumlah;
        $harga       = $request->harga_satuan ?? $detail->harga_satuan;
        $data        = $request->all();
        $data['subtotal'] = $jumlah * $harga;

        $detail->update($data);

        // Sesuaikan stok
        if ($selisih !== 0) {
            $sepeda->decrement('stok', $selisih); // negatif berarti increment
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil diupdate',
            'data'    => $detail
        ], 200);
    }

    /**
     * DELETE /api/detail-transaksi/{id}
     * Menghapus item dari transaksi
     */
    public function destroy($id)
    {
        $detail = DetailTransaksi::find($id);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail transaksi dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        // Kembalikan stok sepeda saat item dihapus
        $sepeda = Sepeda::find($detail->sepeda_id);
        if ($sepeda) {
            $sepeda->increment('stok', $detail->jumlah);
        }

        $detail->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari transaksi'
        ], 200);
    }
}