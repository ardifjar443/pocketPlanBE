<?php

namespace App\Http\Controllers;

use App\Models\KategoriPendapatan;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class KategoriPendapatanController extends Controller
{

    public function getKategoriPendapatan()
    {
        $kategoriPendapatan = KategoriPendapatan::all();

        if ($kategoriPendapatan->isEmpty()) {
            return response()->json([
                'message' => 'gagal mendapatkan data kategori pendapatan',
                'error' => 'tidak ada data kategori'
            ], 404);  // Respons dengan kode status 404 jika tidak ada data
        }
        return response()->json([
            "message" => "berhasil mendapatkan data kategori pendapatan",
            "data" => $kategoriPendapatan
        ]);
    }


    public function tambahKategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|'
        ]);

        // Jika validasi gagal, kirim respons error
        if ($validator->fails()) {
            return response()->json([
                'message' => 'gagal menambahkan data kategori pendapatan',
                'errors' => 'data kategori anda tidak valid'
            ], 422);
        }

        $id_user = Auth::id();

        $kategoriPendapatan = KategoriPendapatan::create(
            [
                'nama_kategori' => $request->nama_kategori,
                'id_user' => $id_user
            ]
        );

        return response()->json([
            'message' => 'berhasil menambahkan data kategori pendapatan'
        ], 201);
    }

    public function getKategoriPendapatanById($id)
    {
        $kategori = KategoriPendapatan::find($id);

        if (!$kategori) {
            return response()->json([
                'message' => 'Kategori pendapatan tidak ditemukan',
                'error' => 'Kategori pendapatan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'berhasil mendapatkan data kategori pendapatan',
            'data' => $kategori
        ], 200);
    }
}
