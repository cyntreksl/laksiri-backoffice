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
        Schema::table('hbl', function (Blueprint $table) {
            $table->boolean('is_short_load')->default(false)->after('is_driver_assigned');
            $table->boolean('is_unmanifest')->default(false)->after('is_short_load');
            $table->boolean('is_overland')->default(false)->after('is_unmanifest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropColumn(['is_short_load', 'is_unmanifest', 'is_overland']);
        });
    }
};
