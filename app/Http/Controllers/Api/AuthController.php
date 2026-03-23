<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // POST /api/v1/auth/login
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)
                    ->with('po')
                    ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email atau password salah.',
                'data'    => null,
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
                'data'    => null,
            ], 403);
        }

        // Hapus token lama sebelum buat yang baru (single session)
        $user->tokens()->delete();

        $token = $user->createToken(
            name: 'api-token',
            abilities: [$user->role],
        )->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil.',
            'data'    => [
                'token' => $token,
                'user'  => new UserResource($user),
            ],
        ]);
    }

    // POST /api/v1/auth/logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil.',
            'data'    => null,
        ]);
    }

    // GET /api/v1/auth/me
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('po');

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => new UserResource($user),
        ]);
    }

    // PUT /api/v1/auth/me/password
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Password saat ini tidak sesuai.',
                'data'    => null,
            ], 422);
        }

        $user->update(['password' => $request->new_password]);

        // Revoke semua token lain kecuali yang sedang dipakai
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Password berhasil diubah.',
            'data'    => null,
        ]);
    }
}