<?php

namespace App\Console\Commands;

use App\Services\TripGeneratorService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyTrips extends Command
{
    protected $signature = 'trips:generate
                            {--date=       : Tanggal spesifik format Y-m-d. Default: besok}
                            {--days=1      : Jumlah hari ke depan yang di-generate (max 31)}
                            {--start-date= : Tanggal mulai untuk range (Y-m-d)}
                            {--end-date=   : Tanggal akhir untuk range (Y-m-d)}
                            {--po=         : Filter generate untuk PO ID tertentu saja}
                            {--dry-run     : Simulasi tanpa benar-benar membuat trip}';

    protected $description = 'Generate trip harian dari seri yang aktif';

    public function __construct(
        private readonly TripGeneratorService $generator
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $poId   = $this->option('po') ? (int) $this->option('po') : null;
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚠  DRY RUN MODE — tidak ada trip yang benar-benar dibuat.');
        }

        // -------------------------------------------------------------------------
        // Tentukan range tanggal dari opsi yang diberikan
        // -------------------------------------------------------------------------
        [$startDate, $endDate] = $this->resolveDateRange();

        if (! $startDate) {
            return Command::FAILURE;
        }

        // -------------------------------------------------------------------------
        // Validasi max 31 hari
        // -------------------------------------------------------------------------
        $totalDays = $startDate->diffInDays($endDate) + 1;

        if ($totalDays > 31) {
            $this->error("Maksimal generate 31 hari sekaligus. Anda meminta {$totalDays} hari.");
            return Command::FAILURE;
        }

        // -------------------------------------------------------------------------
        // Tampilkan info sebelum jalan
        // -------------------------------------------------------------------------
        $this->info('TengGO! Trip Generator');
        $this->line('─────────────────────────────────────────');
        $this->line('  Tanggal  : ' . $startDate->toDateString() . ($totalDays > 1 ? ' s/d ' . $endDate->toDateString() : ''));
        $this->line('  Hari     : ' . $totalDays);
        $this->line('  PO       : ' . ($poId ? "PO #{$poId}" : 'Semua PO'));
        $this->line('─────────────────────────────────────────');

        if ($dryRun) {
            $this->info('Dry run selesai. Jalankan tanpa --dry-run untuk generate sesungguhnya.');
            return Command::SUCCESS;
        }

        // -------------------------------------------------------------------------
        // Generate
        // -------------------------------------------------------------------------
        $this->output->write('Memproses... ');

        $startTime = microtime(true);
        $result    = $this->generator->generateForRange($startDate, $endDate, $poId);
        $elapsed   = round(microtime(true) - $startTime, 2);

        $this->line('selesai dalam ' . $elapsed . 's');
        $this->line('');

        // -------------------------------------------------------------------------
        // Tampilkan hasil
        // -------------------------------------------------------------------------
        $this->line('  Generated : <fg=green>' . $result['generated'] . ' trip</>');
        $this->line('  Skipped   : <fg=yellow>' . $result['skipped'] . ' trip</> (sudah ada / tidak aktif)');

        if (! empty($result['errors'])) {
            $this->line('  Errors    : <fg=red>' . count($result['errors']) . '</>');
            $this->line('');
            $this->error('Detail error:');
            foreach ($result['errors'] as $error) {
                $this->line("  • {$error}");
            }
            $this->line('');
            return Command::FAILURE;
        }

        $this->line('');
        $this->info('Generate berhasil.');

        return Command::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Resolve tanggal dari kombinasi opsi yang mungkin:
    //   --date          → 1 tanggal spesifik
    //   --days          → N hari mulai besok (atau mulai --date kalau diisi)
    //   --start-date + --end-date → range eksplisit
    // -------------------------------------------------------------------------
    private function resolveDateRange(): array
    {
        // Prioritas 1: --start-date & --end-date
        if ($this->option('start-date') && $this->option('end-date')) {
            $start = Carbon::parse($this->option('start-date'));
            $end   = Carbon::parse($this->option('end-date'));

            if ($end->lt($start)) {
                $this->error('--end-date tidak boleh sebelum --start-date.');
                return [null, null];
            }

            return [$start, $end];
        }

        // Prioritas 2: --date (1 tanggal spesifik)
        if ($this->option('date')) {
            $date = Carbon::parse($this->option('date'));
            return [$date, $date->copy()];
        }

        // Prioritas 3: --days dari besok
        $days  = max(1, (int) $this->option('days'));
        $start = now()->addDay()->startOfDay();
        $end   = $start->copy()->addDays($days - 1);

        return [$start, $end];
    }
}