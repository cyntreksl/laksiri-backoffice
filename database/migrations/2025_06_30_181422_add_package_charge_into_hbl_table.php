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
            $table->float('package_charges')->default(0.0)->after('bill_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropColumn('package_charges');
        });
    }
};
