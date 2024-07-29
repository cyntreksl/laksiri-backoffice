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
        if (Schema::hasColumn('branch_prices', 'destination_charges')) {
            Schema::table('branch_prices', function (Blueprint $table) {
                $table->dropColumn('destination_charges');
            });
        }

        Schema::table('branch_prices', function (Blueprint $table) {
            $table->string('volume_charges')
                ->after('bill_vat')
                ->nullable();
            $table->string('per_package_charges')
                ->after('bill_vat')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_prices', function (Blueprint $table) {
            $table->dropColumn('volume_charges');
            $table->dropColumn('per_package_charges');
        });

        Schema::table('branch_prices', function (Blueprint $table) {
            $table->string('destination_charges')
                ->after('bill_vat')
                ->nullable();
        });
    }
};
