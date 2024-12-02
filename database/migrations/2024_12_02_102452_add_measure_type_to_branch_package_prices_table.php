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
        Schema::table('branch_package_prices', function (Blueprint $table) {
            $table->string('measureType', 10)->nullable()->after('bill_vat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_package_prices', function (Blueprint $table) {
            $table->dropColumn('measureType');
        });
    }
};
