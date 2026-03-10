<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PelangganController extends Controller
{
    /**
     * GET /api/pelanggan
     * Menampilkan semua data pelanggan
     */
    public function index()
    {
        $pelanggan = Pelanggan::all();

        return response()->json([
            'success' => true,
            'message' => 'Data semua pelanggan berhasil diambil',
            'data'    => $pelanggan
        ], 200);
    }

    /**
     * GET /api/pelanggan/{id}
     * Menampilkan detail satu pelanggan beserta riwayat transaksinya
     */
    public function show($id)
    {
        $pelanggan = Pelanggan::with('transaksi')->find($id);

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diambil',
            'data'    => $pelanggan
        ], 200);
    }

    /**
     * POST /api/pelanggan
     * Mendaftarkan pelanggan baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'         => 'required|string|max:100',
            'email'        => 'required|email|unique:pelanggans,email',
            'no_telepon'   => 'required|string|max:15',
            'alamat'       => 'required|string',
            'tgl_lahir'    => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $pelanggan = Pelanggan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil ditambahkan',
            'data'    => $pelanggan
        ], 201);
    }

    /**
     * PUT/PATCH /api/pelanggan/{id}
     * Mengupdate data pelanggan berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama'       => 'sometimes|string|max:100',
            'email'      => 'sometimes|email|unique:pelanggans,email,' . $id,
            'no_telepon' => 'sometimes|string|max:15',
            'alamat'     => 'sometimes|string',
            'tgl_lahir'  => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $pelanggan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diupdate',
            'data'    => $pelanggan
        ], 200);
    }

    /**
     * DELETE /api/pelanggan/{id}
     * Menghapus data pelanggan berdasarkan ID
     */
    public function destroy($id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        $pelanggan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil dihapus'
        ], 200);
    }
}