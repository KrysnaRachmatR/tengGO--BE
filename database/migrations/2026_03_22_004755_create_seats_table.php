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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('armada_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_type_id')->constrained()->cascadeOnDelete();

            $table->string('seat_number'); // A1, B2, dll

            $table->timestamps();

            $table->unique(['armada_id', 'seat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
