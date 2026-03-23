<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesSchedule\BulkSeriesScheduleRequest;
use App\Http\Requests\SeriesSchedule\ToggleSeriesScheduleRequest;
use App\Http\Resources\SeriesScheduleResource;
use App\Models\Series;
use App\Models\SeriesSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeriesScheduleController extends Controller
{
    // GET /api/v1/series/{series}/schedules?month=2025-01
    // Mengembalikan list override jadwal per bulan
    // Frontend bisa pakai ini untuk render kalender aktif/nonaktif
    public function index(Request $request, int $seriesId): JsonResponse
    {
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $series = Series::findOrFail($seriesId);

        $query = SeriesSchedule::where('series_id', $series->id)
                               ->with('createdBy');

        if ($request->month) {
            $query->inMonth($request->month);
        } else {
            // Default: tampilkan bulan ini
            $query->inMonth(now()->format('Y-m'));
        }

        $schedules = $query->orderBy('date')->get();

        // Kembalikan juga info seri supaya frontend bisa tau operating_days defaultnya
        return response()->json([
            'status'  => 'success',
            'message' => 'OK',
            'data'    => [
                'series' => [
                    'id'             => $series->id,
                    'name'           => $series->name,
                    'operating_days' => $series->operating_days, // null = setiap hari
                    'is_active'      => $series->is_active,
                ],
                'overrides' => SeriesScheduleResource::collection($schedules),
            ],
        ]);
    }

    // POST /api/v1/series/{series}/schedules/bulk
    // Set aktif/nonaktif untuk banyak tanggal sekaligus
    public function bulk(BulkSeriesScheduleRequest $request, int $seriesId): JsonResponse
    {
        $series = Series::findOrFail($seriesId);
        abort_unless($series->po_id === $request->user()->po_id, 403, 'Akses ditolak.');

        $data      = $request->validated();
        $createdBy = $request->user()->id;
        $affected  = 0;

        DB::transaction(function () use ($series, $data, $createdBy, &$affected) {
            foreach ($data['dates'] as $date) {
                SeriesSchedule::updateOrCreate(
                    [
                        'series_id' => $series->id,
                        'date'      => $date,
                    ],
                    [
                        'po_id'      => $series->po_id,
                        'is_active'  => $data['is_active'],
                        'reason'     => $data['reason'] ?? null,
                        'created_by' => $createdBy,
                    ]
                );
                $affected++;
            }
        });

        $action = $data['is_active'] ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'status'  => 'success',
            'message' => "{$affected} tanggal berhasil {$action}.",
            'data'    => ['affected' => $affected],
        ]);
    }

    // PATCH /api/v1/series/{series}/schedules/{date}
    // Toggle 1 tanggal — date di URL dalam format YYYY-MM-DD
    public function toggle(ToggleSeriesScheduleRequest $request, int $seriesId, string $date): JsonResponse
    {
        // Validasi format tanggal dari URL
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Format tanggal tidak valid. Gunakan YYYY-MM-DD.',
                'data'    => null,
            ], 422);
        }

        $series = Series::findOrFail($seriesId);
        abort_unless($series->po_id === $request->user()->po_id, 403, 'Akses ditolak.');

        $schedule = SeriesSchedule::updateOrCreate(
            [
                'series_id' => $series->id,
                'date'      => $date,
            ],
            [
                'po_id'      => $series->po_id,
                'is_active'  => $request->is_active,
                'reason'     => $request->reason,
                'created_by' => $request->user()->id,
            ]
        );

        $action = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'status'  => 'success',
            'message' => "Seri pada tanggal {$date} berhasil {$action}.",
            'data'    => new SeriesScheduleResource($schedule->load('createdBy')),
        ]);
    }

    // DELETE /api/v1/series/{series}/schedules/{date}
    // Hapus override — seri kembali ke jadwal default operating_days-nya
    public function destroy(Request $request, int $seriesId, string $date): JsonResponse
    {
        abort_unless($request->user()->isAdminPo(), 403, 'Akses ditolak.');

        $series   = Series::findOrFail($seriesId);
        $schedule = SeriesSchedule::where('series_id', $series->id)
                                  ->where('date', $date)
                                  ->firstOrFail();

        $schedule->delete();

        return response()->json([
            'status'  => 'success',
            'message' => "Override jadwal tanggal {$date} berhasil dihapus. Seri kembali ke jadwal default.",
            'data'    => null,
        ]);
    }
}