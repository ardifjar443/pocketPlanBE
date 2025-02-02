<?php

namespace App\Http\Controllers;

use App\Models\KategoriPendapatan;
use App\Models\KategoriPengeluaran;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekomendasiController extends Controller
{
    public function getRekomendasi()
    {
        $pendapatan = $this->getTotalPendapatan();
        $pengeluaran = $this->getTotalPengeluaran();
        $kategoriPendapatan = $this->getKategoriPendapatan();
        $kategoriPengeluaran = $this->getKategoriPengeluaran();


        if (!$pendapatan || !$pengeluaran || !$kategoriPendapatan || !$kategoriPengeluaran) {
            return response()->json([
                'message' => 'Data tidak cukup',
            ], 400); // Mengembalikan status error 400 (Bad Request)

        }
        $rekomendasi = $this->generateRekomendasi($pendapatan, $pengeluaran, $kategoriPendapatan, $kategoriPengeluaran);

        return response()->json([
            'message' => 'Berhasil Mendapatkan Rekomendasi',
            'data' => [
                'perbandingan' => [
                    'pendapatan' => $pendapatan,
                    'pengeluaran' => $pengeluaran
                ],
                'kategori_pendapatan_tertinggi' => $kategoriPendapatan,
                'kategori_pengeluaran_tertinggi' => $kategoriPengeluaran,
                'rekomendasi' => $rekomendasi
            ]
        ], 200);
    }

    public function getTotalPendapatan()
    {
        $data_pendapatan = Pendapatan::whereYear('tanggal', Carbon::now()->year)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->where('id_user', Auth::id())
            ->with('kategori_pendapatan')
            ->get();

        $total_pendapatan = $data_pendapatan->sum('pendapatan');

        return $total_pendapatan;
    }

    public function getTotalPengeluaran()
    {
        $data_pengeluaran = Pengeluaran::whereYear('tanggal', Carbon::now()->year)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->where('id_user', Auth::id())
            ->with('kategori_pengeluaran')
            ->get();

        $total_pengeluaran = $data_pengeluaran->sum('pengeluaran');

        return $total_pengeluaran;
    }

    public function getKategoriPendapatan()
    {
        $tahun = Carbon::now()->year;
        $bulan = Carbon::now()->month;
        $kategori = KategoriPendapatan::withSum([
            'pendapatan as total_pendapatan' => function ($query) use ($tahun, $bulan) {
                $query->where('id_user', Auth::id())
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan);
            }
        ], 'pendapatan')->get();

        // Filter kategori yang memiliki total_pendapatan tidak null
        $kategori = $kategori->filter(function ($kategori) {
            return $kategori->total_pendapatan !== null;
        });

        $kategori_tertinggi = $kategori->sortByDesc('total_pendapatan')->first();

        return $kategori_tertinggi;
    }

    public function getKategoriPengeluaran()
    {
        $tahun = Carbon::now()->year;
        $bulan = Carbon::now()->month;
        $kategori = KategoriPengeluaran::withSum([
            'pengeluaran as total_pengeluaran' => function ($query) use ($tahun, $bulan) {
                $query->where('id_user', Auth::id())
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan);
            }
        ], 'pengeluaran')->get();

        // Filter kategori yang memiliki total_pengeluaran tidak null
        $kategori = $kategori->filter(function ($kategori) {
            return $kategori->total_pengeluaran !== null;
        });

        $kategori_tertinggi = $kategori->sortByDesc('total_pengeluaran')->first();

        return $kategori_tertinggi;
    }

    private function generateRekomendasi($pendapatan, $pengeluaran, $kategoriPendapatan, $kategoriPengeluaran)
    {
        $rekomendasi = [];

        // 1. Rekomendasi dari perbandingan pendapatan dan pengeluaran
        if ($pengeluaran > $pendapatan) {
            $rekomendasi[] = "Pengeluaran Anda lebih besar dari pendapatan. Pertimbangkan untuk mengurangi pengeluaran atau mencari tambahan pendapatan.";
        } elseif ($pendapatan > $pengeluaran) {
            $rekomendasi[] = "Pendapatan Anda lebih besar dari pengeluaran. Pertimbangkan untuk menabung atau berinvestasi.";
        } else {
            $rekomendasi[] = "Pendapatan dan pengeluaran Anda seimbang. Pastikan Anda memiliki dana darurat yang cukup.";
        }

        // 2. Rekomendasi berdasarkan kategori pengeluaran tertinggi
        if ($kategoriPengeluaran) {
            $rekomendasi[] = "Anda memiliki pengeluaran terbesar pada kategori " . $kategoriPengeluaran->nama_kategori . " sebesar Rp" . number_format($kategoriPengeluaran->total_pengeluaran) . ". Cobalah untuk mengurangi pengeluaran di kategori ini.";
        }

        // 3. Rekomendasi berdasarkan kategori pendapatan tertinggi
        if ($kategoriPendapatan) {
            $rekomendasi[] = "Pendapatan terbesar Anda berasal dari " . $kategoriPendapatan->nama_kategori . " sebesar Rp" . number_format($kategoriPendapatan->total_pendapatan) . ". Anda bisa fokus untuk meningkatkan pendapatan dari kategori ini.";
        }

        return $rekomendasi;
    }
}
