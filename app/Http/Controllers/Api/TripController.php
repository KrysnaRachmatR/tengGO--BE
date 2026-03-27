<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\AssignCrewRequest;
use App\Http\Requests\Trip\GenerateTripRequest;
use App\Http\Requests\Trip\StoreTripRequest;
use App\Http\Requests\Trip\UpdateTripRequest;
use App\Http\Requests\Trip\UpdateTripStatusRequest;
use App\Http\Resources\CrewResource;
use App\Http\Resources\TripResource;
use App\Models\Crew;
use App\Models\Series;
use App\Models\Trip;
use App\Models\TripCrew;
use App\Services\TripGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function __construct(
        private readonly TripGeneratorService $generator
    ) {}

    // GET /api/v1/trips
    // Query params: date, series_id, status, fleet_id, origin_city, destination_city
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date'       => ['nullable', 'date_format:Y-m-d'],
            'status'     => ['nullable', 'in:scheduled,running,done,cancelled'],
            'series_id'  => ['nullable', 'integer'],
            'fleet_id'   => ['nullable', 'integer'],
        ]);

        $trips = Trip::query()
            ->when($request->date, fn($q) => $q->onDate($request->date))
            ->when($request->status, fn($q) => $q->status($request->status))
            ->when($request->series_id, fn($q) => $q->where('series_id', $request->series_id))
            ->when($request->fleet_id, fn($q) => $q->where('fleet_id', $request->fleet_id))
            ->when($request->origin_city, fn($q) => $q->where('origin_city', 'like', "%{$request->origin_city}%"))
            ->when($request->destination_city, fn($q) => $q->where('destination_city', 'like', "%{$request->destination_city}%"))
            ->with(['series', 'fleet', 'crews'])
            ->orderBy('trip_date')
            ->orderBy('scheduled_departure')
            ->paginate(20);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => [
                'items' => TripResource::collection($trips->items()),
                'meta'  => [
                    'current_page' => $trips->currentPage(),
                    'per_page'     => $trips->perPage(),
                    'total'        => $trips->total(),
                ],
            ],
        ]);
    }

    // POST /api/v1/trips/generate
    // Generate trip otomatis dari seri aktif untuk 1 tanggal atau range tanggal
    public function generate(GenerateTripRequest $request): JsonResponse
    {
        $poId = $request->user()->po_id;

        if ($request->filled('date')) {
            // Generate 1 tanggal
            $result = $this->generator->generateForDate(
                Carbon::parse($request->date),
                $poId
            );
            $message = "Generate selesai untuk tanggal {$request->date}.";
        } else {
            // Generate range tanggal
            $start  = Carbon::parse($request->start_date);
            $end    = Carbon::parse($request->end_date);
            $days   = $start->diffInDays($end) + 1;

            // Batasi max 31 hari sekaligus
            if ($days > 31) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Maksimal generate 31 hari sekaligus.',
                    'data'    => null,
                ], 422);
            }

            $result  = $this->generator->generateForRange($start, $end, $poId);
            $message = "Generate selesai untuk {$days} hari ({$request->start_date} s/d {$request->end_date}).";
        }

        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => [
                'generated' => $result['generated'],
                'skipped'   => $result['skipped'],
                'errors'    => $result['errors'],
            ],
        ]);
    }

    // POST /api/v1/trips
    // Buat trip manual (override — source: manual)
    public function store(StoreTripRequest $request): JsonResponse
    {
        $series = Series::findOrFail($request->series_id);

        // Pastikan series milik PO yang sama
        abort_unless($series->po_id === $request->user()->po_id, 403, 'Akses ditolak.');

        // Cek apakah trip sudah ada untuk seri + tanggal ini
        $exists = Trip::where('series_id', $series->id)
                      ->where('trip_date', $request->trip_date)
                      ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Trip untuk seri ini pada tanggal tersebut sudah ada.',
                'data'    => null,
            ], 422);
        }

        $trip = Trip::fromSeries($series, $request->trip_date);
        $trip->source     = Trip::SOURCE_MANUAL;
        $trip->fleet_id   = $request->fleet_id;
        $trip->notes      = $request->notes;
        $trip->created_by = $request->user()->id;
        $trip->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Trip berhasil dibuat.',
            'data'    => new TripResource($trip->load(['series', 'fleet', 'crews'])),
        ], 201);
    }

    // GET /api/v1/trips/{trip}
    public function show(int $tripId): JsonResponse
    {
        $trip = Trip::with(['series', 'fleet', 'fleet.seats', 'crews', 'createdBy'])
                    ->findOrFail($tripId);

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => new TripResource($trip),
        ]);
    }

    // PUT /api/v1/trips/{trip}
    // Update armada, jam aktual, catatan
    public function update(UpdateTripRequest $request, int $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        // Tidak bisa edit trip yang sudah selesai atau dibatalkan
        if ($trip->isDone() || $trip->isCancelled()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Trip yang sudah selesai atau dibatalkan tidak bisa diubah.',
                'data'    => null,
            ], 422);
        }

        // Validasi fleet milik PO yang sama
        if ($request->filled('fleet_id')) {
            abort_unless(
                \App\Models\Fleet::where('id', $request->fleet_id)
                                 ->where('po_id', $request->user()->po_id)
                                 ->exists(),
                422,
                'Armada tidak ditemukan atau bukan milik PO ini.'
            );
        }

        $trip->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Trip berhasil diperbarui.',
            'data'    => new TripResource($trip->fresh(['series', 'fleet', 'crews'])),
        ]);
    }

    // PATCH /api/v1/trips/{trip}/status
    public function updateStatus(UpdateTripStatusRequest $request, int $tripId): JsonResponse
    {
        $trip      = Trip::findOrFail($tripId);
        $newStatus = $request->status;

        // Validasi transisi status
        if (! $trip->canTransitionTo($newStatus)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Status tidak bisa diubah dari '{$trip->status}' ke '{$newStatus}'.",
                'data'    => null,
            ], 422);
        }

        $updateData = [
            'status'     => $newStatus,
            'updated_by' => $request->user()->id,
        ];

        // Auto-set actual_departure kalau status → running
        if ($newStatus === Trip::STATUS_RUNNING && ! $trip->actual_departure) {
            $updateData['actual_departure'] = now();
        }

        // Auto-set actual_arrival kalau status → done
        if ($newStatus === Trip::STATUS_DONE && ! $trip->actual_arrival) {
            $updateData['actual_arrival'] = now();
        }

        // Simpan alasan kalau dibatalkan
        if ($newStatus === Trip::STATUS_CANCELLED) {
            $updateData['cancellation_reason'] = $request->cancellation_reason;
        }

        $trip->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => "Status trip berhasil diubah ke '{$newStatus}'.",
            'data'    => new TripResource($trip->fresh(['series', 'fleet', 'crews'])),
        ]);
    }

    // DELETE /api/v1/trips/{trip}
    public function destroy(Request $request, int $tripId): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Hanya Admin PO yang bisa menghapus trip.');

        $trip = Trip::findOrFail($tripId);

        if ($trip->isRunning()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Trip yang sedang berjalan tidak bisa dihapus. Batalkan terlebih dahulu.',
                'data'    => null,
            ], 422);
        }

        $trip->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Trip berhasil dihapus.',
            'data'    => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Crew Assignment
    // -------------------------------------------------------------------------

    // GET /api/v1/trips/{trip}/crews
    public function crewIndex(int $tripId): JsonResponse
    {
        $trip  = Trip::findOrFail($tripId);
        $crews = $trip->crews()->orderBy('trip_crews.role')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => CrewResource::collection($crews),
        ]);
    }

    // POST /api/v1/trips/{trip}/crews
    public function assignCrew(AssignCrewRequest $request, int $tripId): JsonResponse
    {
        $trip = Trip::findOrFail($tripId);

        if ($trip->isDone() || $trip->isCancelled()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Crew tidak bisa di-assign ke trip yang sudah selesai atau dibatalkan.',
                'data'    => null,
            ], 422);
        }

        // Validasi crew milik PO yang sama
        $crew = Crew::findOrFail($request->crew_id);
        abort_unless($crew->po_id === $request->user()->po_id, 403, 'Crew bukan milik PO ini.');

        // Cek apakah crew sudah ada di trip ini
        $alreadyAssigned = TripCrew::where('trip_id', $trip->id)
                                   ->where('crew_id', $crew->id)
                                   ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'status'  => 'error',
                'message' => "{$crew->name} sudah terdaftar di trip ini.",
                'data'    => null,
            ], 422);
        }

        // Kalau is_primary = true, lepas primary lama untuk role yang sama
        if ($request->boolean('is_primary', false)) {
            TripCrew::where('trip_id', $trip->id)
                    ->where('role', $request->role)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
        }

        TripCrew::create([
            'po_id'      => $request->user()->po_id,
            'trip_id'    => $trip->id,
            'crew_id'    => $crew->id,
            'role'       => $request->role,
            'is_primary' => $request->boolean('is_primary', false),
            'notes'      => $request->notes,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "{$crew->name} berhasil di-assign ke trip.",
            'data'    => new TripResource($trip->fresh(['series', 'fleet', 'crews'])),
        ], 201);
    }

    // DELETE /api/v1/trips/{trip}/crews/{crew}
    public function removeCrew(Request $request, int $tripId, int $crewId): JsonResponse
    {
        $trip     = Trip::findOrFail($tripId);
        $tripCrew = TripCrew::where('trip_id', $trip->id)
                            ->where('crew_id', $crewId)
                            ->firstOrFail();

        if ($trip->isRunning() && $tripCrew->is_primary) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Crew utama tidak bisa dilepas dari trip yang sedang berjalan.',
                'data'    => null,
            ], 422);
        }

        $tripCrew->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Crew berhasil dilepas dari trip.',
            'data'    => null,
        ]);
    }
}