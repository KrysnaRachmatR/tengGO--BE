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
        Schema::create('meal_service_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meal_service_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['crew', 'passenger']);
            $table->integer('total_served');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_service_details');
    }
};
