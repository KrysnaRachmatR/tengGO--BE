<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1 seri = 1 rute + 1 arah
        // Contoh:
        //   Rute MALANG-JAKARTA → Seri "THE SUNSET" (kode: MLG/TIMUR THE SUNSET)
        //   Rute JAKARTA-MALANG → Seri "THE SUNSET" (kode: JKT/BARAT THE SUNSET) ← seri terpisah!
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')
                  ->constrained('pos')
                  ->cascadeOnDelete();
            $table->foreignId('route_id')
                  ->constrained('routes')
                  ->cascadeOnDelete();

            $table->string('name');                          // nama seri, misal "THE SUNSET"
            $table->string('code', 50)->nullable();          // kode seri, misal "MLG/TIMUR THE SUNSET"

            // Jam & kota — fixed per seri, bisa di-override di trip kalau ada perubahan mendadak
            $table->time('departure_time');                  // jam keberangkatan, misal "13:00:00"
            $table->string('origin_city');                   // kota awal, misal "Malang"
            $table->string('destination_city');              // tujuan akhir, misal "Poris Plawad"

            // Hari operasi — null berarti setiap hari
            // JSON array: [0,1,2,3,4,5,6] → 0=Minggu, 1=Senin, ..., 6=Sabtu
            $table->json('operating_days')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['po_id', 'code']);
            $table->index(['route_id', 'is_active']);
            $table->index(['po_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
