<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username'
        ]);

        // Jika validasi gagal, kirim respons error
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }


        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ]);


        return response()->json([
            'message' => "berhasil menambahkan User",
            'user' => $user,
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Cari pengguna berdasarkan email
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            // Jika email tidak ditemukan
            return response()->json([
                'message' => 'Login gagal',
                'error' => 'username tidak ada'
            ], 422);
        }


        // Cek apakah password cocok
        if (!Hash::check($request->password, $user->password)) {
            // Jika password salah
            return response()->json([
                'message' => 'Login gagal',
                'error' => 'password salah'
            ], 422);
        }

        if (Auth::attempt($request->only('username', 'password'))) {
            // $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => "Login Berhasil",
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    "id" => $user->id,
                    "username" => $user->username,
                    "saldo" => $user->saldo
                ]
            ], 202);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
