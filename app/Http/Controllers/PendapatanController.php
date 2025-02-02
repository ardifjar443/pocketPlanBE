<?php

namespace App\Http\Controllers;

use App\Models\Pendapatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PendapatanController extends Controller
{
    public function getPendapatan(Request $request)
    {
        $tahun = $request->tahun;
        $bulan = $request->bulan;

        if ($tahun && $bulan) {
            $request->validate([
                'tahun' => 'required|integer',
                'bulan' => 'required|integer|min:1|max:12',
            ]);

            // Query untuk mendapatkan data berdasarkan tahun dan bulan pada kolom `tanggal`
            $data = Pendapatan::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->where('id_user', Auth::id())
                ->with('kategori_pendapatan')
                ->orderBy('tanggal', 'asc') // Mengurutkan berdasarkan tanggal (ascending)
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'gagal mendapatkan data pendapatan',
                    'error' => 'tidak ada data pendapatan pada tahun ' . $tahun . ' dan bulan ' . $bulan
                ], 404);  // Respons dengan kode status 404 jika tidak ada data
            }

            // Mengelompokkan data berdasarkan tanggal
            $groupedData = $data->groupBy(function ($item) {
                return now()::parse($item->tanggal)->format('Y-m-d'); // Mengelompokkan berdasarkan tanggal dalam format 'YYYY-MM-DD'
            });

            // Mengubah struktur data agar menjadi array dengan tanggal sebagai key
            $formattedData = $groupedData->map(function ($items, $date) {
                $totalPerTanggal = $items->sum('pendapatan'); // Total pendapatan untuk tanggal tertentu
                return [
                    'tanggal' => $date,
                    'total_per_tanggal' => $totalPerTanggal, // Menambahkan total pendapatan per tanggal
                    'data_pendapatan' => $items->values(), // Menggunakan values() agar indeks tetap berurutan
                ];
            })->values();

            $totalPendapatan = $data->sum('pendapatan');

            return response()->json([
                'message' => "berhasil mendapatkan data pendapatan tahun " . $tahun . " dan bulan " . $bulan,
                'total_pendapatan' => $totalPendapatan,
                'data' => $formattedData,
            ]);
        } else {
            $data = Pendapatan::where('id_user', Auth::id())
                ->with('kategori_pendapatan')
                ->orderBy('tanggal', 'asc') // Mengurutkan berdasarkan tanggal (ascending)
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'gagal mendapatkan data pendapatan',
                    'error' => 'tidak ada data pendapatan pada '
                ], 404);  // Respons dengan kode status 404 jika tidak ada data
            }

            // Mengelompokkan data berdasarkan tanggal
            $groupedData = $data->groupBy(function ($item) {
                return now()::parse($item->tanggal)->format('Y-m-d'); // Mengelompokkan berdasarkan tanggal dalam format 'YYYY-MM-DD'
            });

            // Mengubah struktur data agar menjadi array dengan tanggal sebagai key
            $formattedData = $groupedData->map(function ($items, $date) {
                $totalPerTanggal = $items->sum('pendapatan'); // Total pendapatan untuk tanggal tertentu
                return [
                    'tanggal' => $date,
                    'total_per_tanggal' => $totalPerTanggal, // Menambahkan total pendapatan per tanggal
                    'data_pendapatan' => $items->values(), // Menggunakan values() agar indeks tetap berurutan
                ];
            })->values();

            $totalPendapatan = $data->sum('pendapatan');

            return response()->json([
                'message' => "berhasil mendapatkan data pendapatan tahun " . $tahun . " dan bulan " . $bulan,
                'total_pendapatan' => $totalPendapatan,
                'data' => $formattedData,
            ]);
        }
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


            // $id_user = Auth::id();

            $pendapatan = Pendapatan::create(
                [
                    'pendapatan' => $request->pendapatan,
                    'id_user' => Auth::id(),
                    'id_kategori_pendapatan' => $request->id_kategori_pendapatan,
                    'tanggal' => $request->tanggal
                ]
            );


            $user = User::find(Auth::id());
            $saldo = $user->saldo;
            $user->update([
                'saldo' => $saldo + $request->pendapatan
            ]);

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

                $saldo_awal = $pendapatan->pendapatan;
                $user = User::find(Auth::id());
                $saldo = $user->saldo;
                $user->update([
                    'saldo' => $saldo - $saldo_awal + $request->pendapatan
                ]);


                $pendapatan->update([
                    'pendapatan' => $request->pendapatan,
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

                $user = User::find(Auth::id());
                $saldo = $user->saldo;

                $user->update([
                    'saldo' => $saldo - $pendapatan->pendapatan
                ]);

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
    public function getPendapatanByTahunBulan(Request $request)
    {
        // Validasi parameter input
        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        // Query untuk mendapatkan data berdasarkan tahun dan bulan pada kolom `tanggal`
        $data = Pendapatan::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getPendapatanSummary()
    {
        // Query untuk mendapatkan data berdasarkan tahun dan bulan pada kolom `tanggal`
        $data = Pendapatan::whereYear('tanggal', Carbon::now()->year)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->where('id_user', Auth::id())
            ->sum('pendapatan');


        return response()->json([
            'message' => " berhasil mendapatkan data pendapatan tahun " . Carbon::now()->year . " dan bulan " . Carbon::now()->month,
            'data' => [
                'total_pendapatan' => $data
            ],
        ]);
    }
}
