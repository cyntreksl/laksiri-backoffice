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
        Schema::table('containers', function (Blueprint $table) {
            $table->after('status', function (Blueprint $table) {
                $table->timestamp('arrived_at_primary_warehouse')->nullable();
                $table->integer('arrived_primary_warehouse_by')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn(['arrived_at_primary_warehouse', 'arrived_primary_warehouse_by']);
        });
    }
};
