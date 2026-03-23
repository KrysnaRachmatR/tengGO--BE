<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\StoreFleetRequest;
use App\Http\Requests\Fleet\UpdateFleetFacilitiesRequest;
use App\Http\Requests\Fleet\UpdateFleetRequest;
use App\Http\Requests\Fleet\UpdateFleetSeatsRequest;
use App\Http\Resources\FleetResource;
use App\Models\Fleet;
use App\Models\FleetSeat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FleetController extends Controller
{
    // GET /api/v1/fleets
    public function index(Request $request): JsonResponse
    {
        $fleets = Fleet::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->with('seats')
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => [
                'items' => FleetResource::collection($fleets->items()),
                'meta'  => [
                    'current_page' => $fleets->currentPage(),
                    'per_page'     => $fleets->perPage(),
                    'total'        => $fleets->total(),
                ],
            ],
        ]);
    }

    // POST /api/v1/fleets
    public function store(StoreFleetRequest $request): JsonResponse
    {
        DB::transaction(function () use ($request, &$fleet) {
            $fleet = Fleet::create($request->safe()->except('seats'));

            if ($request->filled('seats')) {
                $this->syncSeats($fleet, $request->seats);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Armada berhasil ditambahkan.',
            'data'    => new FleetResource($fleet->load('seats')),
        ], 201);
    }

    // GET /api/v1/fleets/{fleet}
    public function show(int $fleetId): JsonResponse
    {
        $fleet = Fleet::with('seats')->findOrFail($fleetId);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => new FleetResource($fleet),
        ]);
    }

    // PUT /api/v1/fleets/{fleet}
    public function update(UpdateFleetRequest $request, int $fleetId): JsonResponse
    {
        $fleet = Fleet::findOrFail($fleetId);
        $fleet->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Armada berhasil diperbarui.',
            'data'    => new FleetResource($fleet->fresh('seats')),
        ]);
    }

    // DELETE /api/v1/fleets/{fleet}
    public function destroy(Request $request, int $fleetId): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Akses ditolak.');

        $fleet = Fleet::findOrFail($fleetId);

        // Tidak bisa hapus armada yang sedang dipakai trip aktif
        $isOnActiveTrip = $fleet->trips()
            ->whereIn('status', ['scheduled', 'running'])
            ->exists();

        if ($isOnActiveTrip) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Armada tidak bisa dihapus karena sedang digunakan pada trip yang aktif.',
                'data'    => null,
            ], 422);
        }

        $fleet->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Armada berhasil dihapus.',
            'data'    => null,
        ]);
    }

    // PUT /api/v1/fleets/{fleet}/seat-config
    // Replace seluruh konfigurasi kursi — sync penuh
    public function updateSeats(UpdateFleetSeatsRequest $request, int $fleetId): JsonResponse
    {
        $fleet = Fleet::findOrFail($fleetId);

        DB::transaction(function () use ($fleet, $request) {
            $this->syncSeats($fleet, $request->seats);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Konfigurasi kursi berhasil diperbarui.',
            'data'    => new FleetResource($fleet->fresh('seats')),
        ]);
    }

    // PUT /api/v1/fleets/{fleet}/facilities
    public function updateFacilities(UpdateFleetFacilitiesRequest $request, int $fleetId): JsonResponse
    {
        $fleet = Fleet::findOrFail($fleetId);
        $fleet->update(['facilities' => $request->facilities]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Fasilitas armada berhasil diperbarui.',
            'data'    => new FleetResource($fleet->fresh('seats')),
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    // Sync penuh seat config — delete yang tidak ada di request, upsert yang ada
    private function syncSeats(Fleet $fleet, array $seats): void
    {
        $incomingTypes = collect($seats)->pluck('type')->toArray();

        // Hapus tipe yang tidak ada di request baru
        $fleet->seats()->whereNotIn('type', $incomingTypes)->delete();

        // Upsert tipe yang ada
        foreach ($seats as $index => $seat) {
            FleetSeat::updateOrCreate(
                [
                    'fleet_id' => $fleet->id,
                    'type'     => $seat['type'],
                ],
                [
                    'class_name'  => $seat['class_name'],
                    'total'       => $seat['total'],
                    'price_base'  => $seat['price_base'] ?? 0,
                    'seat_layout' => $seat['seat_layout'] ?? null,
                    'sort_order'  => $seat['sort_order'] ?? $index,
                ]
            );
        }

        // Recalculate total_seats di tabel fleets
        $fleet->syncTotalSeats();
    }
}