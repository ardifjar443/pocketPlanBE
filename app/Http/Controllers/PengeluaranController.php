<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengeluaranController extends Controller
{
    public function getPengeluaran(Request $request)
    {

        $tahun = $request->tahun;
        $bulan = $request->bulan;

        if ($tahun && $bulan) {
            $request->validate([
                'tahun' => 'required|integer',
                'bulan' => 'required|integer|min:1|max:12',
            ]);

            // Query untuk mendapatkan data berdasarkan tahun dan bulan pada kolom `tanggal`
            $data = Pengeluaran::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->where('id_user', Auth::id())
                ->with('kategori_pengeluaran')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'gagal mendapatkan data pengeluaran',
                    'error' => 'tidak ada data pengeluaran pada tahun ' . $tahun . ' dan bulan ' . $bulan
                ], 404);  // Respons dengan kode status 404 jika tidak ada data
            }

            // Mengelompokkan data berdasarkan tanggal
            $groupedData = $data->groupBy(function ($item) {
                return now()::parse($item->tanggal)->day; // Mengelompokkan berdasarkan tanggal
            })->map(function ($items, $day) {
                return $items; // Tetap menyimpan semua data pada tanggal tertentu
            });

            // Mengubah struktur data agar kunci adalah tanggal
            $formattedData = [];
            foreach ($groupedData as $day => $items) {
                $formattedData[$day] = $items; // Memasukkan data dengan key berupa tanggal
            }

            return response()->json([
                'message' => " berhasil mendapatkan data pengeluaran tahun " . $tahun . " dan bulan " . $bulan,
                'data' => $formattedData,
            ]);
        } else {
            $pengeluaran = Pengeluaran::where('id_user', Auth::id())
                ->with("kategori_pengeluaran")
                ->get();

            if ($pengeluaran->isEmpty()) {
                return response()->json([
                    'message' => 'gagal mendapatkan data pengeluaran',
                    'error' => 'tidak ada data pengeluaran'
                ], 404);  // Respons dengan kode status 404 jika tidak ada data
            }
            return response()->json([
                "message" => "berhasil mendapatkan data pengeluaran",
                "data" => $pengeluaran
            ]);
        }
    }

    public function tambahPengeluaran(Request $request)
    {

        try {
            $validated = $request->validate([
                'id_kategori_pengeluaran' => 'required|exists:kategori_pengeluaran,id_kategori_pengeluaran',
                'tanggal' => 'required'
            ], [
                'kategori.exists' => 'kategori yang Anda masukkan tidak ditemukan.',
            ]);


            // $id_user = Auth::id();

            $pengeluaran = Pengeluaran::create(
                [
                    'pengeluaran' => $request->pengeluaran,
                    'id_user' => Auth::id(),
                    'id_kategori_pengeluaran' => $request->id_kategori_pengeluaran,
                    'tanggal' => $request->tanggal
                ]
            );


            $user = User::find(Auth::id());
            $saldo = $user->saldo;
            $user->update([
                'saldo' => $saldo - $request->pengeluaran
            ]);

            return response()->json([
                'message' => 'berhasil menambahkan data pengeluaran'
            ], 201);
        } catch (QueryException $e) {
            // Tangkap error duplikat (kode error MySQL: 23000)
            if ($e->getCode() == '23000') {
                return response()->json([
                    'message' => 'gagal menambahkan data pengeluaran',
                    'error' => 'pengeluaran ini sudah ada'
                ], 422);
            }
        }
    }
    public function updatePengeluaran(Request $request, $id)
    {
        $pengeluaran = Pengeluaran::find($id);
        if ($pengeluaran) {
            if ($pengeluaran->id_user == Auth::id()) {
                $validated = $request->validate([
                    'id_kategori_pengeluaran' => 'exists:kategori_pengeluaran,id_kategori_pengeluaran',
                ], [
                    'kategori.exists' => 'kategori yang Anda masukkan tidak ditemukan.',
                ]);

                $saldo_awal = $pengeluaran->pengeluaran;
                $user = User::find(Auth::id());
                $saldo = $user->saldo;
                $user->update([
                    'saldo' => $saldo + $saldo_awal - $request->pengeluaran
                ]);


                $pengeluaran->update([
                    'pengeluaran' => $request->pengeluaran,
                    'id_kategori_pengeluaran' => $request->id_kategori_pengeluaran,
                    'tanggal' => $request->tanggal
                ]);

                return response()->json([
                    'message' => 'berhasil mengupdate data pengeluaran'
                ]);
            } else {
                return response()->json([
                    'message' => 'gagal mengupdate data pengeluaran',
                    'error' => 'anda bukan pemilik dari pengeluaran ini'
                ]);
            }
        }

        return response()->json([
            'message' => 'gagal mengupdate data pengeluaran',
            'error' => 'data pengeluaran tidak ditemukan'
        ]);
    }

    public function getPengeluaranByid($id)
    {
        $pengeluaran = Pengeluaran::with('kategori_pengeluaran')->find($id);
        if ($pengeluaran) {
            if ($pengeluaran->id_user == Auth::id()) {

                return response()->json([
                    'message' => 'berhasil mendapatkan data kategori pengeluaran dengan id ' . $id,
                    'data' => $pengeluaran
                ]);
            } else {
                return response()->json([
                    'message' => 'gagal mendapatkan  data pengeluaran dengan id' . $id,
                    'error' => 'anda bukan pemilik dari pengeluaran ini'
                ]);
            }
        }

        return response()->json([
            'message' => 'gagal mendapatkan  data pengeluaran dengan id' . $id,
            'error' => 'data pengeluaran tidak ditemukan'
        ]);
    }

    public function deletePengeluaran($id)
    {
        $pengeluaran = Pengeluaran::find($id);
        if ($pengeluaran) {
            if ($pengeluaran->id_user == Auth::id()) {

                $user = User::find(Auth::id());
                $saldo = $user->saldo;

                $user->update([
                    'saldo' => $saldo + $pengeluaran->pengeluaran
                ]);

                $pengeluaran->delete();
                return response()->json([
                    'message' => 'berhasil menghapus data pengeluaran dengan id ' . $id
                ]);
            } else {
                return response()->json([
                    'message' => 'gagal menghapus  data pengeluaran dengan id' . $id,
                    'error' => 'anda bukan pemilik dari pengeluaran ini'
                ]);
            }
        }

        return response()->json([
            'message' => 'gagal menghapus  data pengeluaran dengan id' . $id,
            'error' => 'data pengeluaran tidak ditemukan'
        ]);
    }
}
