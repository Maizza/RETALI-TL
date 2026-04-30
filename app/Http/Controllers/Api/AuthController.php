<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\Tourleader;
use App\Models\Muthawif;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // =========================
        // VALIDASI
        // =========================
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email = trim(strtolower($request->email));
        $password = trim($request->password);

        // =========================
        // CEK TOURLEADER
        // =========================
        $user = Tourleader::with('kloter')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        $role = 'tourleader';

        // =========================
        // CEK MUTHAWIF
        // =========================
        if (!$user) {
            $user = Muthawif::with('kloter')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            $role = 'muthawif';
        }

        // =========================
        // VALIDASI LOGIN
        // =========================
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password salah',
            ], 401);
        }

        // =========================
        // TOKEN SANCTUM
        // =========================
        $token = $user->createToken('auth_token')->plainTextToken;

        // =========================
        // RESPONSE
        // =========================
        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'              => $user->id,
                'name'            => $role === 'tourleader' ? $user->name : $user->nama,
                'email'           => $user->email,
                'role'            => $role,
                'kloter'          => $user->kloter?->nama,
                'kloter_tanggal'  => $user->kloter?->tanggal,
            ],
        ], 200);
    }
}