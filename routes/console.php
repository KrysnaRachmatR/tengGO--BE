<?php

use App\Services\TripGeneratorService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| TengGO! Console Routes
|--------------------------------------------------------------------------
| Closure-based artisan commands untuk keperluan quick maintenance.
| Command utama ada di app/Console/Commands/
*/

// Inspirasi bawaan Laravel
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Quick check: tampilkan ringkasan trip hari ini per PO
Artisan::command('trips:today {--po= : Filter PO ID}', function () {
    $poId = $this->option('po') ? (int) $this->option('po') : null;

    $query = \App\Models\Trip::query()
        ->today()
        ->with('series')
        ->orderBy('scheduled_departure');

    if ($poId) {
        $query->where('po_id', $poId);
    } else {
        $query->withoutGlobalScope('tenant');
    }

    $trips = $query->get();

    if ($trips->isEmpty()) {
        $this->warn('Tidak ada trip hari ini.');
        return;
    }

    $this->info('Trip hari ini (' . now()->toDateString() . '):');
    $this->table(
        ['ID', 'Seri', 'Asal', 'Tujuan', 'Jam', 'Status', 'Armada'],
        $trips->map(fn($t) => [
            $t->id,
            $t->series?->name ?? '-',
            $t->origin_city,
            $t->destination_city,
            $t->scheduled_departure,
            strtoupper($t->status),
            $t->fleet_id ? "Fleet #{$t->fleet_id}" : '-',
        ])
    );

    $this->line('Total: ' . $trips->count() . ' trip');

})->purpose('Tampilkan ringkasan trip hari ini');

// Quick check: cek seri yang tidak punya trip untuk tanggal tertentu
Artisan::command('trips:missing {date? : Tanggal Y-m-d, default hari ini}', function () {
    $date = $this->argument('date') ?? now()->toDateString();

    $seriesWithTrip = \App\Models\Trip::withoutGlobalScope('tenant')
        ->where('trip_date', $date)
        ->pluck('series_id')
        ->toArray();

    $missingSeries = \App\Models\Series::withoutGlobalScope('tenant')
        ->where('is_active', true)
        ->whereNotIn('id', $seriesWithTrip)
        ->with('route', 'po')
        ->get();

    if ($missingSeries->isEmpty()) {
        $this->info("Semua seri aktif sudah punya trip untuk {$date}.");
        return;
    }

    $this->warn("Seri aktif yang BELUM punya trip untuk {$date}:");
    $this->table(
        ['ID', 'PO', 'Seri', 'Rute', 'Jam'],
        $missingSeries->map(fn($s) => [
            $s->id,
            $s->po?->code ?? '-',
            $s->name,
            "{$s->origin_city} → {$s->destination_city}",
            $s->departure_time,
        ])
    );

})->purpose('Cek seri aktif yang belum punya trip di tanggal tertentu');