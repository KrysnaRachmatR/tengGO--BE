<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel ini menyimpan PENGECUALIAN dari jadwal normal seri
        // Kalau seri berjalan normal setiap hari → tidak perlu record di sini
        // Record dibuat ketika ada:
        //   1. Seri dinonaktifkan di tanggal tertentu (libur, dll)
        //   2. Seri diaktifkan di hari yang bukan hari operasinya (trip tambahan)
        Schema::create('series_schedules', function (Blueprint $table) {
            $table->id();

            // po_id ditambahkan untuk SaaS — agar tabel ini bisa di-scope
            // langsung tanpa harus join ke series terlebih dahulu
            $table->foreignId('po_id')
                  ->constrained('pos')
                  ->cascadeOnDelete();

            $table->foreignId('series_id')
                  ->constrained('series')
                  ->cascadeOnDelete();

            $table->date('date');
            $table->boolean('is_active')->default(true);    // false = nonaktif di tanggal ini
            $table->string('reason')->nullable();           // alasan: "Lebaran", "Armada servis", dll

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->unique(['series_id', 'date']);          // 1 seri max 1 record per tanggal
            $table->index(['po_id', 'date']);
            $table->index(['po_id', 'series_id', 'date']);
            $table->index(['series_id', 'date', 'is_active']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_schedules');
    }
};
