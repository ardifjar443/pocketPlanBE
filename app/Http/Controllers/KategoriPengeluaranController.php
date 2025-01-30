<?php

namespace App\Http\Controllers;

use App\Models\KategoriPengeluaran;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KategoriPengeluaranController extends Controller
{
    public function getKategoriPengeluaran(Request $request)
    {
        $tahun = $request->tahun;
        $bulan = $request->bulan;
        if ($tahun && $bulan) {
            $kategori = KategoriPengeluaran::withSum([
                'pengeluaran as total_pengeluaran' => function ($query) use ($tahun, $bulan) {
                    $query->where('id_user', Auth::id())
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $bulan);
                }
            ], 'pengeluaran')
                ->get();


            $kategori = $kategori->filter(function ($kategori) {
                return $kategori->total_pengeluaran !== null;
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
            $KategoriPengeluaran = KategoriPengeluaran::all();

            if ($KategoriPengeluaran->isEmpty()) {
                return response()->json([
                    'message' => 'gagal mendapatkan data kategori pendapatan',
                    'error' => 'tidak ada data kategori'
                ], 404);  // Respons dengan kode status 404 jika tidak ada data
            }
            return response()->json([
                "message" => "berhasil mendapatkan data kategori pendapatan",
                "data" => $KategoriPengeluaran
            ]);
        }
    }

    public function tambahKategori(Request $request)
    {
        try {
            $request->validate([
                'nama_kategori' => 'required|string|'
            ]);

            $kategoriPendapatan = KategoriPengeluaran::create(
                [
                    'nama_kategori' => $request->nama_kategori
                ]
            );

            return response()->json([
                'message' => 'berhasil menambahkan data kategori pengeluaran'
            ], 201);
        } catch (QueryException $e) {
            // Tangkap error duplikat (kode error MySQL: 23000)
            if ($e->getCode() == '23000') {
                return response()->json([
                    'message' => 'gagal menambahkan data kategori pengeluaran',
                    'error' => 'kategori ini sudah ada'
                ], 422);
            }
        }
    }

    public function updateKategoriPengeluaran(Request $request, $id)
    {
        $kategori = KategoriPengeluaran::find($id);

        if ($kategori) {

            $request->validate(
                ['nama_kategori' => 'required']
            );
            $kategori->update([
                'nama_kategori' => $request->nama_kategori
            ]);

            return response()->json([
                'message' => 'berhasil mengupdate data kategori pengeluaran',
            ]);
        }

        return response()->json([
            'message' => 'gagal mengupdate data kategori pengeluaran',
            'error' => 'data kategori tidak ditemukan'
        ]);
    }

    public function deleteKategoriPengeluaran($id)
    {
        $kategori = KategoriPengeluaran::find($id);

        if ($kategori) {

            $kategori->delete();

            return response()->json([
                'message' => 'berhasil menghapus data kategori pengeluaran',
            ]);
        }

        return response()->json([
            'message' => 'gagal menghapus data kategori pengeluaran',
            'error' => 'data kategori tidak ditemukan'
        ]);
    }
}
