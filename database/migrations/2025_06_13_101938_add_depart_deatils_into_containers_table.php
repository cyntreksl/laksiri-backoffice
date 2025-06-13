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
            $table->after('arrived_primary_warehouse_by', function (Blueprint $table) {
                $table->timestamp('departed_at_primary_warehouse')->nullable();
                $table->integer('departed_primary_warehouse_by')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn(['departed_at_primary_warehouse', 'departed_primary_warehouse_by']);
        });
    }
};
