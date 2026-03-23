<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel inti MVP — 1 record = 1 trip jalan pada 1 tanggal
        // Sumber: di-generate otomatis dari series_schedules atau dibuat manual
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')
                  ->constrained('pos')
                  ->cascadeOnDelete();
            $table->foreignId('series_id')
                  ->constrained('series')
                  ->cascadeOnDelete();
            $table->foreignId('fleet_id')
                  ->nullable()                              // nullable: belum di-assign armada
                  ->constrained('fleets')
                  ->nullOnDelete();

            $table->date('trip_date');                      // tanggal trip

            // Snapshot dari series saat trip di-generate
            // Disimpan di sini agar rekap tidak berubah kalau data series di-edit belakangan
            $table->time('scheduled_departure');            // jam berangkat sesuai seri
            $table->string('origin_city');                  // snapshot kota asal
            $table->string('destination_city');             // snapshot kota tujuan

            // Jam aktual — diisi oleh operasional saat trip berjalan
            $table->datetime('actual_departure')->nullable();
            $table->datetime('actual_arrival')->nullable();

            $table->enum('status', [
                'scheduled',                               // trip terjadwal, belum jalan
                'running',                                 // sedang dalam perjalanan
                'done',                                    // selesai
                'cancelled',                               // dibatalkan
            ])->default('scheduled');

            $table->enum('source', [
                'auto',                                    // di-generate oleh scheduler
                'manual',                                  // dibuat manual oleh admin/operasional
            ])->default('auto');

            $table->text('notes')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // 1 seri tidak boleh punya 2 trip di tanggal yang sama
            $table->unique(['series_id', 'trip_date']);

            $table->index(['po_id', 'trip_date', 'status']);
            $table->index(['series_id', 'trip_date']);
            $table->index(['fleet_id', 'trip_date']);
            $table->index('trip_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
