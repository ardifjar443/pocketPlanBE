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
