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
        Schema::table('hbl_packages', function (Blueprint $table) {
            $table->bigInteger('current_warehouse')->nullable()->after('is_unloaded');
            $table->boolean('is_de_loaded')->default(0)->after('current_warehouse');
            $table->boolean('is_de_unloaded')->default(0)->after('is_de_loaded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl_packages', function (Blueprint $table) {
            $table->dropColumn('current_warehouse');
            $table->dropColumn('is_de_loaded');
            $table->dropColumn('is_de_unloaded');
        });
    }
};
