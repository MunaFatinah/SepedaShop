<?php

namespace App\Http\Controllers;

use App\Models\Sepeda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SepedaController extends Controller
{
    /**
     * GET /api/sepeda
     * Menampilkan semua data sepeda
     */
    public function index()
    {
        $sepeda = Sepeda::with('kategori')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data semua sepeda berhasil diambil',
            'data'    => $sepeda
        ], 200);
    }

    /**
     * GET /api/sepeda/{id}
     * Menampilkan detail satu sepeda berdasarkan ID
     */
    public function show($id)
    {
        $sepeda = Sepeda::with('kategori')->find($id);

        if (!$sepeda) {
            return response()->json([
                'success' => false,
                'message' => 'Sepeda dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data sepeda berhasil diambil',
            'data'    => $sepeda
        ], 200);
    }

    /**
     * POST /api/sepeda
     * Menambahkan data sepeda baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_sepeda'  => 'required|string|max:100',
            'merek'        => 'required|string|max:50',
            'kategori_id'  => 'required|exists:kategoris,id',
            'harga'        => 'required|numeric|min:0',
            'stok'         => 'required|integer|min:0',
            'deskripsi'    => 'nullable|string',
            'warna'        => 'nullable|string|max:30',
            'ukuran_roda'  => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $sepeda = Sepeda::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data sepeda berhasil ditambahkan',
            'data'    => $sepeda
        ], 201);
    }

    /**
     * PUT/PATCH /api/sepeda/{id}
     * Mengupdate data sepeda berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        $sepeda = Sepeda::find($id);

        if (!$sepeda) {
            return response()->json([
                'success' => false,
                'message' => 'Sepeda dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_sepeda'  => 'sometimes|string|max:100',
            'merek'        => 'sometimes|string|max:50',
            'kategori_id'  => 'sometimes|exists:kategoris,id',
            'harga'        => 'sometimes|numeric|min:0',
            'stok'         => 'sometimes|integer|min:0',
            'deskripsi'    => 'nullable|string',
            'warna'        => 'nullable|string|max:30',
            'ukuran_roda'  => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $sepeda->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data sepeda berhasil diupdate',
            'data'    => $sepeda
        ], 200);
    }

    /**
     * DELETE /api/sepeda/{id}
     * Menghapus data sepeda berdasarkan ID
     */
    public function destroy($id)
    {
        $sepeda = Sepeda::find($id);

        if (!$sepeda) {
            return response()->json([
                'success' => false,
                'message' => 'Sepeda dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        $sepeda->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data sepeda berhasil dihapus'
        ], 200);
    }
}