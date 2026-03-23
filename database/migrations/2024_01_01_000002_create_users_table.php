<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')
                  ->nullable()                              // null = super admin (lintas PO)
                  ->constrained('pos')
                  ->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', [
                'super_admin',                             // akses semua PO
                'admin_po',                                // manajemen data master & user PO
                'operasional',                             // input trip harian
                'staff',                                   // read-only / akses terbatas
            ])->default('operasional');
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['po_id', 'role']);
            $table->index(['po_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
