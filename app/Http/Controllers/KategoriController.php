<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{
    /**
     * GET /api/kategori
     * Menampilkan semua kategori sepeda
     */
    public function index()
    {
        $kategori = Kategori::withCount('sepeda')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data semua kategori berhasil diambil',
            'data'    => $kategori
        ], 200);
    }

    /**
     * GET /api/kategori/{id}
     * Menampilkan detail satu kategori beserta daftar sepedanya
     */
    public function show($id)
    {
        $kategori = Kategori::with('sepeda')->find($id);

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kategori berhasil diambil',
            'data'    => $kategori
        ], 200);
    }

    /**
     * POST /api/kategori
     * Menambahkan kategori baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:50|unique:kategoris,nama_kategori',
            'deskripsi'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $kategori = Kategori::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data'    => $kategori
        ], 201);
    }

    /**
     * PUT/PATCH /api/kategori/{id}
     * Mengupdate data kategori berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'sometimes|string|max:50|unique:kategoris,nama_kategori,' . $id,
            'deskripsi'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $kategori->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diupdate',
            'data'    => $kategori
        ], 200);
    }

    /**
     * DELETE /api/kategori/{id}
     * Menghapus kategori berdasarkan ID
     */
    public function destroy($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        // Cek apakah kategori masih digunakan oleh sepeda
        if ($kategori->sepeda()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh data sepeda'
            ], 409);
        }

        $kategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus'
        ], 200);
    }
}