<?php

namespace App\Services;

use App\Models\Series;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripGeneratorService
{
    // Generate semua trip untuk 1 tanggal tertentu
    // Dipanggil dari: scheduler harian, atau manual via POST /trips/generate
    public function generateForDate(Carbon $date, ?int $poId = null): array
    {
        $generated = 0;
        $skipped   = 0;
        $errors    = [];

        // Ambil semua seri aktif — scope per PO kalau poId diisi
        $query = Series::query()
            ->where('is_active', true)
            ->with('schedules');

        if ($poId) {
            $query->where('po_id', $poId);
        } else {
            // Kalau dipanggil dari scheduler global, bypass tenant scope
            $query->withoutGlobalScope('tenant');
        }

        $allSeries = $query->get();

        DB::transaction(function () use ($allSeries, $date, &$generated, &$skipped, &$errors) {
            foreach ($allSeries as $series) {
                try {
                    // Cek apakah seri aktif di tanggal ini
                    if (! $series->isActiveOn($date)) {
                        $skipped++;
                        continue;
                    }

                    // Cek apakah trip sudah ada untuk seri + tanggal ini
                    $exists = Trip::withoutGlobalScope('tenant')
                        ->where('series_id', $series->id)
                        ->where('trip_date', $date->toDateString())
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    // Buat trip dari data seri
                    Trip::fromSeries($series, $date->toDateString())->save();
                    $generated++;

                } catch (\Throwable $e) {
                    Log::error("TripGenerator error seri #{$series->id} tanggal {$date->toDateString()}: {$e->getMessage()}");
                    $errors[] = "Seri #{$series->id} ({$series->name}): {$e->getMessage()}";
                }
            }
        });

        return compact('generated', 'skipped', 'errors');
    }

    // Generate untuk range tanggal (misal 1 minggu ke depan)
    public function generateForRange(Carbon $startDate, Carbon $endDate, ?int $poId = null): array
    {
        $totalGenerated = 0;
        $totalSkipped   = 0;
        $totalErrors    = [];

        $date = $startDate->copy();

        while ($date->lte($endDate)) {
            $result = $this->generateForDate($date, $poId);

            $totalGenerated += $result['generated'];
            $totalSkipped   += $result['skipped'];
            $totalErrors     = array_merge($totalErrors, $result['errors']);

            $date->addDay();
        }

        return [
            'generated' => $totalGenerated,
            'skipped'   => $totalSkipped,
            'errors'    => $totalErrors,
        ];
    }
}