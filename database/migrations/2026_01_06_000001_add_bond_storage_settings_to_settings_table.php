<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('bond_storage_sea_serial')->default(1)->after('notification');
            $table->integer('bond_storage_air_serial')->default(1)->after('bond_storage_sea_serial');
            $table->boolean('auto_bond_generation_enabled')->default(false)->after('bond_storage_air_serial');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['bond_storage_sea_serial', 'bond_storage_air_serial', 'auto_bond_generation_enabled']);
        });
    }
};
