<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Po\StoreAdminPoRequest;
use App\Http\Requests\Po\StorePoRequest;
use App\Http\Requests\Po\UpdatePoRequest;
use App\Http\Resources\PoResource;
use App\Http\Resources\UserResource;
use App\Models\Po;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PoController extends Controller
{
    // GET /api/v1/admin/pos
    public function index(Request $request): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $pos = Po::query()
                 ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
                 ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
                 ->withCount(['users', 'routes', 'series'])
                 ->orderBy('name')
                 ->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => [
                'items' => PoResource::collection($pos->items()),
                'meta'  => [
                    'current_page' => $pos->currentPage(),
                    'per_page'     => $pos->perPage(),
                    'total'        => $pos->total(),
                ],
            ],
        ]);
    }

    // POST /api/v1/admin/pos
    public function store(StorePoRequest $request): JsonResponse
    {
        $po = Po::create([
            ...$request->validated(),
            'slug' => $request->slug ?? Str::slug($request->name),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'PO berhasil didaftarkan.',
            'data'    => new PoResource($po),
        ], 201);
    }

    // GET /api/v1/admin/pos/{po}
    public function show(Request $request, int $poId): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $po = Po::withCount(['users', 'routes', 'series', 'fleets', 'crews'])
                ->findOrFail($poId);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => new PoResource($po),
        ]);
    }

    // PUT /api/v1/admin/pos/{po}
    public function update(UpdatePoRequest $request, int $poId): JsonResponse
    {
        $po = Po::findOrFail($poId);
        $po->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'PO berhasil diperbarui.',
            'data'    => new PoResource($po),
        ]);
    }

    // DELETE /api/v1/admin/pos/{po}
    public function destroy(Request $request, int $poId): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $po = Po::findOrFail($poId);
        $po->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'PO berhasil dihapus.',
            'data'    => null,
        ]);
    }

    // POST /api/v1/admin/pos/{po}/admin
    // Daftarkan Admin PO untuk PO tertentu
    public function storeAdmin(StoreAdminPoRequest $request, int $poId): JsonResponse
    {
        $po = Po::findOrFail($poId);

        $user = User::create([
            ...$request->validated(),
            'po_id' => $po->id,
            'role'  => User::ROLE_ADMIN_PO,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Admin PO untuk {$po->name} berhasil didaftarkan.",
            'data'    => new UserResource($user->load('po')),
        ], 201);
    }

    // GET /api/v1/admin/pos/{po}/users
    // List semua user di PO tertentu (super admin)
    public function users(Request $request, int $poId): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $po = Po::findOrFail($poId);

        $users = User::withoutGlobalScope('tenant')
                     ->where('po_id', $po->id)
                     ->when($request->role, fn($q) => $q->where('role', $request->role))
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

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Akses ditolak.');
    }
}