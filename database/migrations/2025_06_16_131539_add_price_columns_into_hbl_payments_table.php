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
        Schema::table('hbl_payments', function (Blueprint $table) {
            $table->after('currency_code', function (Blueprint $table) {
                $table->float('package_charge')->default(0)->nullable();
                $table->float('handling_charge')->default(0)->nullable();
                $table->float('slpa_charge')->default(0)->nullable();
                $table->float('demurrage_charge')->default(0)->nullable();
                $table->float('bond_charge')->default(0)->nullable();
                $table->float('sub_total')->default(0)->nullable()->comment('without tax & discount');
                $table->float('destination_total')->default(0)->nullable();
                $table->json('tax_rates')->nullable();
                $table->float('tax')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl_payments', function (Blueprint $table) {
            $table->dropColumn(['package_charge', 'handling_charge', 'slpa_charge', 'demurrage_charge', 'bond_charge', 'sub_total', 'tax', 'destination_total', 'tax_rates']);
        });
    }
};
