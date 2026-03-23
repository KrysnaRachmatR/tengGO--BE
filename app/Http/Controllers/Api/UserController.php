<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET /api/v1/po/users
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Akses ditolak.');

        $users = User::query()
                     ->when($request->role, fn($q) => $q->role($request->role))
                     ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                         $q->where('name', 'like', "%{$request->search}%")
                           ->orWhere('email', 'like', "%{$request->search}%");
                     }))
                     ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
                     ->orderBy('name')
                     ->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => [
                'items' => UserResource::collection($users->items()),
                'meta'  => [
                    'current_page' => $users->currentPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                ],
            ],
        ]);
    }

    // POST /api/v1/po/users
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            ...$request->validated(),
            'po_id' => $request->user()->po_id, // auto inject po_id dari admin yang login
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'User berhasil ditambahkan.',
            'data'    => new UserResource($user->load('po')),
        ], 201);
    }

    // GET /api/v1/po/users/{user}
    public function show(Request $request, int $userId): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Akses ditolak.');

        // Global scope BelongsToTenant sudah menjamin user yang diakses
        // hanya milik PO yang sama
        $user = User::findOrFail($userId);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => new UserResource($user->load('po')),
        ]);
    }

    // PUT /api/v1/po/users/{user}
    public function update(UpdateUserRequest $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        // Admin PO tidak boleh edit dirinya sendiri lewat endpoint ini
        abort_if($user->id === $request->user()->id, 422, 'Gunakan endpoint /auth/me untuk mengubah data diri sendiri.');

        // Admin PO tidak boleh edit user dengan role di atasnya
        abort_if($user->isSuperAdmin() || $user->isAdminPo(), 403, 'Tidak bisa mengubah data user dengan role ini.');

        $user->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'User berhasil diperbarui.',
            'data'    => new UserResource($user->fresh('po')),
        ]);
    }

    // DELETE /api/v1/po/users/{user}
    public function destroy(Request $request, int $userId): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Akses ditolak.');

        $user = User::findOrFail($userId);

        abort_if($user->id === $request->user()->id, 422, 'Tidak bisa menghapus akun sendiri.');
        abort_if($user->isSuperAdmin() || $user->isAdminPo(), 403, 'Tidak bisa menghapus user dengan role ini.');

        // Revoke semua token sebelum hapus
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'User berhasil dihapus.',
            'data'    => null,
        ]);
    }
}