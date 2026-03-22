<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('domain')->unique()->after('name');
            $table->string('api_key')->unique()->nullable()->after('domain');
            $table->boolean('is_active')->default(true)->after('api_key');

            // optional (buat branding SaaS)
            $table->string('primary_color')->nullable()->after('logo');
            $table->string('secondary_color')->nullable()->after('primary_color');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'domain',
                'api_key',
                'is_active',
                'primary_color',
                'secondary_color',
            ]);
        });
    }
};