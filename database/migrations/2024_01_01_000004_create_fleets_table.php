<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')
                  ->constrained('pos')
                  ->cascadeOnDelete();
            $table->string('name');                         // nama unit, misal "Fanilla", "Coklat"
            $table->string('license_plate', 20)->nullable(); // plat nomor
            $table->string('brand')->nullable();            // Mercedes, Scania, Hino, dll
            $table->string('model')->nullable();            // OH1626, K410, dll
            $table->year('year')->nullable();               // tahun kendaraan
            $table->integer('total_seats')->default(0);     // total kursi (dihitung dari fleet_seats)

            // Fasilitas — JSON agar fleksibel, tiap PO beda-beda
            // contoh: ["ac", "toilet", "wifi", "tv", "mini_pantry", "reclining_seat", "usb_charger"]
            $table->json('facilities')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['po_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleets');
    }
};
