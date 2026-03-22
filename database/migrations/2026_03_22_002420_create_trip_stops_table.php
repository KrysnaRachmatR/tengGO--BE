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
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stop_id')->constrained()->cascadeOnDelete();

            $table->integer('order'); // urutan perjalanan
            $table->timestamp('estimated_time')->nullable();

            $table->enum('type', ['absen', 'makan', 'transit'])->nullable(); 
            // override kalau beda dari master

            $table->timestamps();

            $table->unique(['trip_id', 'order']); // urutan tidak boleh duplicate
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_stops');
    }
};
