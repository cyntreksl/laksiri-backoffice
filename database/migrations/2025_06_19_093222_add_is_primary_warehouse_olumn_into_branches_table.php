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
        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('is_primary_warehouse')->default(false)->after('branch_code')->comment('Indicates if the branch is a primary warehouse');
            $table->float('slpa_charge')->default(0.0)->after('is_prepaid');
            $table->float('handling_charge')->default(0.0)->after('slpa_charge');
            $table->float('bond_charge')->default(0.0)->after('handling_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('is_primary_warehouse');
            $table->dropColumn('slpa_charge');
            $table->dropColumn('handling_charge');
            $table->dropColumn('bond_charge');
        });
    }
};
