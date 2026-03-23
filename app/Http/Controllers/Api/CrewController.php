<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crew\StoreCrewRequest;
use App\Http\Requests\Crew\UpdateCrewRequest;
use App\Http\Resources\CrewResource;
use App\Models\Crew;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrewController extends Controller
{
    // GET /api/v1/crews
    public function index(Request $request): JsonResponse
    {
        $crews = Crew::query()
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%");
            }))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => [
                'items' => CrewResource::collection($crews->items()),
                'meta'  => [
                    'current_page' => $crews->currentPage(),
                    'per_page'     => $crews->perPage(),
                    'total'        => $crews->total(),
                ],
            ],
        ]);
    }

    // POST /api/v1/crews
    public function store(StoreCrewRequest $request): JsonResponse
    {
        $crew = Crew::create($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Crew berhasil ditambahkan.',
            'data'    => new CrewResource($crew),
        ], 201);
    }

    // GET /api/v1/crews/{crew}
    public function show(int $crewId): JsonResponse
    {
        $crew = Crew::findOrFail($crewId);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => new CrewResource($crew),
        ]);
    }

    // PUT /api/v1/crews/{crew}
    public function update(UpdateCrewRequest $request, int $crewId): JsonResponse
    {
        $crew = Crew::findOrFail($crewId);
        $crew->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Data crew berhasil diperbarui.',
            'data'    => new CrewResource($crew->fresh()),
        ]);
    }

    // DELETE /api/v1/crews/{crew}
    public function destroy(Request $request, int $crewId): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Akses ditolak.');

        $crew = Crew::findOrFail($crewId);

        // Tidak bisa hapus crew yang masih assign di trip aktif
        $isOnActiveTrip = $crew->tripCrews()
            ->whereHas('trip', fn($q) => $q->whereIn('status', ['scheduled', 'running']))
            ->exists();

        if ($isOnActiveTrip) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Crew tidak bisa dihapus karena masih terdaftar pada trip yang aktif.',
                'data'    => null,
            ], 422);
        }

        $crew->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Crew berhasil dihapus.',
            'data'    => null,
        ]);
    }
}