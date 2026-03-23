<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot: 1 trip bisa punya banyak crew, 1 crew bisa di banyak trip (hari berbeda)
        // Tapi 1 crew tidak boleh di 2 trip berbeda di tanggal yang sama
        Schema::create('trip_crews', function (Blueprint $table) {
            $table->id();

            // po_id ditambahkan untuk SaaS — agar tabel ini bisa di-scope
            // langsung tanpa harus join ke trips terlebih dahulu
            $table->foreignId('po_id')
                  ->constrained('pos')
                  ->cascadeOnDelete();

            $table->foreignId('trip_id')
                  ->constrained('trips')
                  ->cascadeOnDelete();
            $table->foreignId('crew_id')
                  ->constrained('crews')
                  ->cascadeOnDelete();

            // Role crew di trip ini — bisa beda dengan role default crew-nya
            // Contoh: co_driver menggantikan driver utama yang tidak hadir
            $table->enum('role', [
                'driver',
                'co_driver',
                'conductor',
                'guide',
            ]);

            $table->boolean('is_primary')->default(false);  // penanda crew utama per role
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['trip_id', 'crew_id']);          // 1 crew max 1 kali per trip
            $table->index(['po_id', 'trip_id']);
            $table->index(['po_id', 'crew_id']);
            $table->index(['crew_id', 'trip_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_crews');
    }
};
