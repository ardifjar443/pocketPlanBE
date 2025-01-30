<?php

use App\Http\Controllers\KategoriPendapatanController;
use App\Http\Controllers\KategoriPengeluaranController;
use App\Http\Controllers\PendapatanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout']);

    // kategori pendapatan
    Route::get('/kategori/pendapatan', [KategoriPendapatanController::class, 'getKategoriPendapatan']); // Get all
    Route::post('/kategori/pendapatan', [KategoriPendapatanController::class, 'tambahKategori']);
    Route::get('/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'getKategoriPendapatanById']);
    Route::put('/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'updateKategoriPendapatan']);
    Route::delete('/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'deleteKategoriPendapatan']);

    //pendapatan
    Route::get('/pendapatan', [PendapatanController::class, 'getPendapatan']);
    Route::get('/pendapatan/summary', [PendapatanController::class, 'getPendapatanSummary']);
    Route::post('/pendapatan', [PendapatanController::class, 'tambahPendapatan']);
    Route::put('/pendapatan/{id}', [PendapatanController::class, 'updatePendapatan']);
    Route::get('/pendapatan/{id}', [PendapatanController::class, 'getPendapatanById']);
    Route::delete('/pendapatan/{id}', [PendapatanController::class, 'deletePendapatan']);

    //kategori  pengeluaran
    Route::get('/kategori/pengeluaran', [KategoriPengeluaranController::class, 'getKategoriPengeluaran']);
    Route::post('/kategori/pengeluaran', [KategoriPengeluaranController::class, 'tambahKategori']);
    Route::put('/kategori/pengeluaran/{id}', [KategoriPengeluaranController::class, 'updateKategoriPengeluaran']);
    Route::delete('/kategori/pengeluaran/{id}', [KategoriPengeluaranController::class, 'deleteKategoriPengeluaran']);

    // pengeluaran
    Route::get('/pengeluaran', [PengeluaranController::class,  'getPengeluaran']);
    Route::post('/pengeluaran', [PengeluaranController::class,  'tambahPengeluaran']);
    Route::put('/pengeluaran/{id}', [PengeluaranController::class,  'updatePengeluaran']);
    Route::get('/pengeluaran/{id}', [PengeluaranController::class,  'getPengeluaranById']);
    Route::delete('/pengeluaran/{id}', [PengeluaranController::class,  'deletePengeluaran']);
});
