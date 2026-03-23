<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel ini menyimpan tipe-tipe kursi yang ada di 1 armada
        //
        // Contoh bus "Fanilla":
        //   type=executive  class_name="Executive Plus"   total=20
        //   type=sleeper    class_name="Sleeper VIP"      total=8
        //   type=queen      class_name="Queen Seat 1-1-1" total=4
        //
        // `type`       → identifier standar, dipakai untuk filter & logika sistem
        // `class_name` → nama tampil bebas per PO, dipakai untuk UI & dokumen
        Schema::create('fleet_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_id')
                  ->constrained('fleets')
                  ->cascadeOnDelete();

            // Identifier standar — tipe bawaan: executive, sleeper, queen
            // Admin PO bebas tambah tipe custom (vip, ekonomi, patas, dll)
            $table->string('type', 30);

            // Nama tampil bebas per PO
            // Contoh: "Sleeper VIP", "Executive Plus", "Queen Seat 1-1-1"
            $table->string('class_name', 50);

            $table->integer('total')->default(0);           // jumlah kursi tipe ini
            $table->integer('price_base')->default(0);      // harga dasar (referensi ticketing nanti)

            // Layout kursi untuk ticketing nanti (opsional di MVP)
            // Format: "1A,1B,1C|2A,2B,2C" — tiap baris dipisah pipe, tiap kursi dipisah koma
            $table->text('seat_layout')->nullable();

            $table->integer('sort_order')->default(0);      // urutan tampil (premium dulu)
            $table->timestamps();

            // 1 armada tidak boleh punya 2 entri dengan type yang sama
            $table->unique(['fleet_id', 'type']);
            $table->index('fleet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_seats');
    }
};
