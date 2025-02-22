<?php

namespace App\Http\Controllers;

use App\Models\KategoriPendapatan;
use Illuminate\Auth\Events\Validated;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class KategoriPendapatanController extends Controller
{

    public function getKategoriPendapatan(Request $request)
    {
        $tahun = $request->tahun;
        $bulan = $request->bulan;
        if ($tahun && $bulan) {
            $kategori = KategoriPendapatan::withSum([
                'pendapatan as total_pendapatan' => function ($query) use ($tahun, $bulan) {
                    $query->where('id_user', Auth::id())
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $bulan);
                }
            ], 'pendapatan')
                ->get();


            $kategori = $kategori->filter(function ($kategori) {
                return $kategori->total_pendapatan !== null;
            });

            if ($kategori->isEmpty()) {
                return response()->json([
                    'message' => 'gagal mendapatkan summary data kategori pendapatan',
                    'error' => 'tidak ada data pendapatan'
                ], 404);
            }

            return response()->json([
                'message' => 'berhasil mendapatkan summary data kategori pendapatan',
                'data' => $kategori
            ]);
        } else {
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
    }


    public function tambahKategori(Request $request)
    {
        try {
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

            $kategoriPendapatan = KategoriPendapatan::create(
                [
                    'nama_kategori' => $request->nama_kategori
                ]
            );

            return response()->json([
                'message' => 'berhasil menambahkan data kategori pendapatan'
            ], 201);
        } catch (QueryException $e) {
            // Tangkap error duplikat (kode error MySQL: 23000)
            if ($e->getCode() == '23000') {
                return response()->json([
                    'message' => 'gagal menambahkan data kategori pendapatan',
                    'error' => 'kategori ini sudah ada'
                ], 422);
            }
        }
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

    public function updateKategoriPendapatan(Request $request, $id)
    {
        $kategori = KategoriPendapatan::find($id);

        if ($kategori) {

            $request->validate(
                ['nama_kategori' => 'required']
            );
            $kategori->update([
                'nama_kategori' => $request->nama_kategori
            ]);

            return response()->json([
                'message' => 'berhasil mengupdate data kategori pendapatan',
            ]);
        }

        return response()->json([
            'message' => 'gagal mengupdate data kategori pendapatan',
            'error' => 'data kategori tidak ditemukan'
        ]);
    }

    public function deleteKategoriPendapatan($id)
    {
        $kategori = KategoriPendapatan::find($id);

        if ($kategori) {

            $kategori->delete();

            return response()->json([
                'message' => 'berhasil menghapus data kategori pendapatan',
            ]);

            return response()->json([
                'message' => 'gagal menghapus data kategori pendapatan',
                'error' => 'anda bukan pemilik kategori ini'
            ]);
        }

        return response()->json([
            'message' => 'gagal menghapus data kategori pendapatan',
            'error' => 'data kategori tidak ditemukan'
        ]);
    }
}
