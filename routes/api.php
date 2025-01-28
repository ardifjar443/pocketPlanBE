<?php

use App\Http\Controllers\KategoriPendapatanController;
use App\Http\Controllers\PendapatanController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // kategori pendapatan
    Route::get('/kategori/pendapatan', [KategoriPendapatanController::class, 'getKategoriPendapatan']); // Get all
    Route::post('/kategori/pendapatan', [KategoriPendapatanController::class, 'tambahKategori']);
    Route::get('/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'getKategoriPendapatanById']);
    Route::put('/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'updateKategoriPendapatan']);
    Route::delete('/kategori/pendapatan/{id}', [KategoriPendapatanController::class, 'deleteKategoriPendapatan']);

    //pendapatan
    Route::get('/pendapatan', [PendapatanController::class, 'getPendapatan']);
    Route::post('/pendapatan', [PendapatanController::class, 'tambahPendapatan']);
    Route::put('/pendapatan/{id}', [PendapatanController::class, 'updatePendapatan']);
    Route::get('/pendapatan/{id}', [PendapatanController::class, 'getPendapatanById']);
    Route::delete('/pendapatan/{id}', [PendapatanController::class, 'deletePendapatan']);
});
