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
        Schema::create('trip_crews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crew_id')->constrained()->cascadeOnDelete();

            $table->string('role')->nullable(); // fleksibel (driver utama, cadangan, dll)

            $table->timestamps();

            $table->unique(['trip_id', 'crew_id']); // prevent duplicate assign
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_crews');
    }
};
