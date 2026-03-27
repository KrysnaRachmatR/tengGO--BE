<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    // -------------------------------------------------------------------------
    // Daftarkan semua custom Artisan commands
    // -------------------------------------------------------------------------
    protected $commands = [
        Commands\GenerateDailyTrips::class,
    ];

    // -------------------------------------------------------------------------
    // Jadwal otomatis
    // -------------------------------------------------------------------------
    protected function schedule(Schedule $schedule): void
    {
        // Generate trip untuk BESOK setiap tengah malam
        // Berjalan setiap hari jam 00:00 server time
        $schedule->command('trips:generate --days=1')
                 ->dailyAt('00:00')
                 ->withoutOverlapping()        // tidak jalan dobel kalau sebelumnya belum selesai
                 ->runInBackground()           // tidak blocking
                 ->appendOutputTo(storage_path('logs/trip-generator.log'));

        // Opsional: generate 7 hari ke depan setiap Senin pagi
        // Berguna sebagai safety net kalau scheduler sempat mati beberapa hari
        $schedule->command('trips:generate --days=7')
                 ->weeklyOn(1, '01:00')        // Senin jam 01:00
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/trip-generator.log'));
    }

    // -------------------------------------------------------------------------
    // Bootstrap commands
    // -------------------------------------------------------------------------
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}