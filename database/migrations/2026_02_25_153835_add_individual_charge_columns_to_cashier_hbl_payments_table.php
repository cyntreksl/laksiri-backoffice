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
        Schema::table('cashier_hbl_payments', function (Blueprint $table) {
            // Departure Charges Columns
            $table->decimal('departure_freight_charge', 10, 2)->nullable()->after('note');
            $table->decimal('departure_bill_charge', 10, 2)->nullable();
            $table->decimal('departure_package_charge', 10, 2)->nullable();
            $table->decimal('departure_discount', 10, 2)->nullable();
            $table->decimal('departure_additional_charges', 10, 2)->nullable();
            $table->decimal('departure_grand_total', 10, 2)->nullable();
            $table->string('departure_base_currency_code', 10)->nullable();
            $table->decimal('departure_base_currency_rate_in_lkr', 10, 4)->nullable();
            $table->boolean('departure_is_branch_prepaid')->nullable();

            // Destination Charges Columns - Group 1
            $table->decimal('destination_handling_charge', 10, 2)->nullable();
            $table->decimal('destination_slpa_charge', 10, 2)->nullable();
            $table->decimal('destination_bond_charge', 10, 2)->nullable();
            $table->decimal('destination_1_total', 10, 2)->nullable();
            $table->decimal('destination_1_tax', 10, 2)->nullable();
            $table->decimal('destination_1_total_with_tax', 10, 2)->nullable();

            // Destination Charges Columns - Group 2
            $table->decimal('destination_do_charge', 10, 2)->nullable();
            $table->decimal('destination_demurrage_charge', 10, 2)->nullable();
            $table->decimal('destination_stamp_charge', 10, 2)->nullable();
            $table->decimal('destination_other_charge', 10, 2)->nullable();
            $table->decimal('destination_2_total', 10, 2)->nullable();
            $table->decimal('destination_2_tax', 10, 2)->nullable();
            $table->decimal('destination_2_total_with_tax', 10, 2)->nullable();

            // Destination Charges - Additional Info
            $table->string('destination_base_currency_code', 10)->nullable();
            $table->decimal('destination_base_currency_rate_in_lkr', 10, 4)->nullable();
            $table->boolean('destination_is_branch_prepaid')->nullable();
            $table->json('destination_applicable_taxes')->nullable();
            $table->timestamp('destination_stop_demurrage_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_hbl_payments', function (Blueprint $table) {
            $table->dropColumn([
                // Departure charges
                'departure_freight_charge',
                'departure_bill_charge',
                'departure_package_charge',
                'departure_discount',
                'departure_additional_charges',
                'departure_grand_total',
                'departure_base_currency_code',
                'departure_base_currency_rate_in_lkr',
                'departure_is_branch_prepaid',

                // Destination charges
                'destination_handling_charge',
                'destination_slpa_charge',
                'destination_bond_charge',
                'destination_1_total',
                'destination_1_tax',
                'destination_1_total_with_tax',
                'destination_do_charge',
                'destination_demurrage_charge',
                'destination_stamp_charge',
                'destination_other_charge',
                'destination_2_total',
                'destination_2_tax',
                'destination_2_total_with_tax',
                'destination_base_currency_code',
                'destination_base_currency_rate_in_lkr',
                'destination_is_branch_prepaid',
                'destination_applicable_taxes',
                'destination_stop_demurrage_at',
            ]);
        });
    }
};
