<?php

use App\Http\Controllers\KategoriPendapatanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PendapatanController;
use App\Models\KategoriPendapatan;

Route::get('/', function () {
    try {
        DB::connection()->getPdo();
        return view('welcome', ['version' => '1.0.0']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Database connection failed', 'error' => $e->getMessage()], 500);
    }
});

Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/kategori/pendapatan', [KategoriPendapatanController::class, 'getKategoriPendapatan']); // Get all
    Route::post('/api/kategori/pendapatan', [KategoriPendapatanController::class, 'tambahKategori']);
    Route::get('/api/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'getKategoriPendapatanById']);
    Route::put('/api/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'updateKategoriPendapatan']);
    Route::delete('api/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'deleteKategoriPendapatan']);
});
