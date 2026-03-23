<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')
                  ->constrained('pos')
                  ->cascadeOnDelete();
            $table->string('origin');                       // kota asal, misal "Malang"
            $table->string('destination');                  // kota tujuan, misal "Jakarta"
            $table->string('name')->nullable();             // override nama, misal "MALANG - JAKARTA"
                                                            // kalau null, generate dari origin-destination
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // 1 PO tidak boleh punya rute duplikat arah yang sama
            $table->unique(['po_id', 'origin', 'destination']);
            $table->index(['po_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
