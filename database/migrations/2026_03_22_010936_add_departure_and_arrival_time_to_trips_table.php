<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dateTime('departure_time')
                ->nullable()
                ->after('schedule_id');

            $table->dateTime('arrival_time')
                ->nullable()
                ->after('departure_time');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['departure_time', 'arrival_time']);
        });
    }
};