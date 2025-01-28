<?php

namespace App\Http\Controllers;

use App\Models\Pendapatan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PendapatanController extends Controller
{
    public function getPendapatan()
    {
        $pendapatan = Pendapatan::where('id_user', Auth::id())
            ->with("kategori_pendapatan")
            ->get();

        if ($pendapatan->isEmpty()) {
            return response()->json([
                'message' => 'gagal mendapatkan data pendapatan',
                'error' => 'tidak ada data pendapatan'
            ], 404);  // Respons dengan kode status 404 jika tidak ada data
        }
        return response()->json([
            "message" => "berhasil mendapatkan data pendapatan",
            "data" => $pendapatan
        ]);
    }

    public function tambahPendapatan(Request $request)
    {

        try {
            $validated = $request->validate([
                'id_kategori_pendapatan' => 'required|exists:kategori_pendapatan,id_kategori_pendapatan',
                'tanggal' => 'required'
            ], [
                'kategori.exists' => 'kategori yang Anda masukkan tidak ditemukan.',
            ]);


            $id_user = Auth::id();

            $pendapatan = Pendapatan::create(
                [
                    'pendapatan' => $request->pendapatan,
                    'id_user' => $id_user,
                    'id_kategori_pendapatan' => $request->id_kategori_pendapatan,
                    'tanggal' => $request->tanggal
                ]
            );

            return response()->json([
                'message' => 'berhasil menambahkan data pendapatan'
            ], 201);
        } catch (QueryException $e) {
            // Tangkap error duplikat (kode error MySQL: 23000)
            if ($e->getCode() == '23000') {
                return response()->json([
                    'message' => 'gagal menambahkan data pendapatan',
                    'error' => 'pendapatan ini sudah ada'
                ], 422);
            }
        }
    }
    public function updatePendapatan(Request $request, $id)
    {
        $pendapatan = Pendapatan::find($id);
        if ($pendapatan) {
            if ($pendapatan->id_user == Auth::id()) {
                $validated = $request->validate([
                    'id_kategori_pendapatan' => 'required|exists:kategori_pendapatan,id_kategori_pendapatan',
                    'tanggal' => 'required'
                ], [
                    'kategori.exists' => 'kategori yang Anda masukkan tidak ditemukan.',
                ]);
                $pendapatan->update([
                    'jumlah_pendapatan' => $request->jumlah_pendapatan,
                    'id_kategori_pendapatan' => $request->id_kategori_pendapatan,
                    'tanggal' => $request->tanggal
                ]);

                return response()->json([
                    'message' => 'berhasil mengupdate data pendapatan'
                ]);
            } else {
                return response()->json([
                    'message' => 'gagal mengupdate data pendapatan',
                    'error' => 'anda bukan pemilik dari pendapatan ini'
                ]);
            }
        }

        return response()->json([
            'message' => 'gagal mengupdate data pendapatan',
            'error' => 'data pendapatan tidak ditemukan'
        ]);
    }
    public function getPendapatanByid($id)
    {
        $pendapatan = Pendapatan::with('kategori_pendapatan')->find($id);
        if ($pendapatan) {
            if ($pendapatan->id_user == Auth::id()) {

                return response()->json([
                    'message' => 'berhasil mendapatkan data kategori pendapatan dengan id ' . $id,
                    'data' => $pendapatan
                ]);
            } else {
                return response()->json([
                    'message' => 'gagal mendapatkan  data pendapatan dengan id' . $id,
                    'error' => 'anda bukan pemilik dari pendapatan ini'
                ]);
            }
        }

        return response()->json([
            'message' => 'gagal mendapatkan  data pendapatan dengan id' . $id,
            'error' => 'data kategori tidak ditemukan'
        ]);
    }
    public function deletePendapatan($id)
    {
        $pendapatan = Pendapatan::with('kategori_pendapatan')->find($id);
        if ($pendapatan) {
            if ($pendapatan->id_user == Auth::id()) {
                $pendapatan->delete();
                return response()->json([
                    'message' => 'berhasil menghapus data pendapatan dengan id ' . $id
                ]);
            } else {
                return response()->json([
                    'message' => 'gagal menghapus  data pendapatan dengan id' . $id,
                    'error' => 'anda bukan pemilik dari pendapatan ini'
                ]);
            }
        }

        return response()->json([
            'message' => 'gagal menghapus  data pendapatan dengan id' . $id,
            'error' => 'data pendapatan tidak ditemukan'
        ]);
    }
}
