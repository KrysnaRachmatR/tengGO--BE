<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->foreignId('armada_id')->constrained()->cascadeOnDelete();

            $table->date('departure_date');     // tanggal jalan
            $table->time('departure_time')->nullable();

            $table->decimal('price', 10, 2)->nullable(); // optional MVP

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            // 🚀 optimization
            $table->index(['company_id', 'departure_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
