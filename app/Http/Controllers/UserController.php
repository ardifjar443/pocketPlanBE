<?php

namespace App\Http\Controllers;

use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username'
        ]);
        if (!$request->name) {
            return response()->json([
                'message' => 'Login gagal',
                'error' => 'name tidak diisi'
            ], 422);
        }
        if (!$request->username) {
            return response()->json([
                'message' => 'Login gagal',
                'error' => 'username tidak diisi'
            ], 422);
        }

        if (!$request->password) {
            return response()->json([
                'message' => 'Login gagal',
                'error' => 'password tidak diisi'
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'error' => 'username sudah ada'
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
        // $request->validate([
        //     'username' => 'required',
        //     'password' => 'required',
        // ]);

        if (!$request->username) {
            return response()->json([
                'message' => 'Login gagal',
                'error' => 'username tidak diisi'
            ], 422);
        }

        if (!$request->password) {
            return response()->json([
                'message' => 'Login gagal',
                'error' => 'password tidak diisi'
            ], 422);
        }

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
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            // Tentukan masa expired token (1 bulan)
            $user->tokens->last()->expires_at = Carbon::now()->addMonth();
            $user->tokens->last()->save();

            return response()->json([
                'message' => "Login Berhasil",
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    "id" => $user->id_user,
                    "username" => $user->username,
                    "saldo" => $user->saldo
                ]
            ], 202);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function updateUser(Request $request)
    {
        $user = User::find(Auth::id());
        if ($user) {
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                // 'password' => bcrypt($request->password),
            ]);

            return response()->json([
                'message' => 'berhasil mengupdate data user'
            ]);
        }

        return response()->json([
            'message' => 'gagal mengupdate data user',
            'error' => 'data user tidak ditemukan'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ], 200);
    }

    public function getUser()
    {
        $user = User::find(Auth::id());
        $data_pendapatan = Pendapatan::whereYear('tanggal', Carbon::now()->year)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->where('id_user', Auth::id())
            ->with('kategori_pendapatan')
            ->get();

        $total_pendapatan = $data_pendapatan->sum('pendapatan');

        $data_pengeluaran = Pengeluaran::whereYear('tanggal', Carbon::now()->year)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->where('id_user', Auth::id())
            ->with('kategori_pengeluaran')
            ->get();

        $total_pengeluaran = $data_pengeluaran->sum('pengeluaran');

        return response()->json([
            'message' => 'berhasil mendapatkan data user',
            'data' => $user,
            'data_keuangan' => [
                'total_pendapatan' => $total_pendapatan,
                'total_pengeluaran' => $total_pengeluaran
            ]
        ], 200);
    }

    public function verifyToken(Request $request)
    {
        $token = $request->bearerToken();

        if ($token) {
            try {
                $accessToken = PersonalAccessToken::findToken($token);



                if ($accessToken && !$accessToken->expires_at || Carbon::parse($accessToken->expires_at)->isFuture()) {
                    // Perpanjang masa expired token selama 1 bulan
                    $accessToken->forceFill([
                        'expires_at' => Carbon::now()->addMonth()->toDateTimeString()
                    ])->save();

                    return response()->json(['message' => 'Token valid and extended'], 200);
                }


                return response()->json(['message' => 'Token expired or invalid'], 401);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Token verification failed', 'error' => $e], 500);
            }
        }

        return response()->json(['message' => 'Token not provided'], 400);
    }
}
