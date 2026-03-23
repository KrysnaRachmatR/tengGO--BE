<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')
                  ->constrained('pos')
                  ->cascadeOnDelete();
            $table->string('name');
            $table->string('nik', 20)->nullable();           // nomor identitas
            $table->string('phone', 20)->nullable();
            $table->enum('role', [
                'driver',                                    // supir utama
                'co_driver',                                 // supir cadangan
                'helper',                                 // kondektur / kenek
                'pramugara/ri',                             // pemandu (opsional)
            ])->default('driver');
            $table->string('license_number', 30)->nullable(); // SIM
            $table->date('license_expiry')->nullable();       // masa berlaku SIM
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['po_id', 'role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crews');
    }
};
