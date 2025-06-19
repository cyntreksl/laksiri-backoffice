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
        Schema::create('hbl_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hbl_id')->comment('Foreign key to the HBL table');
            $table->unsignedBigInteger('branch_id');
            $table->char('base_currency_code', 3);
            $table->float('base_currency_rate_in_lkr')->default(1.0);
            $table->boolean('is_branch_prepaid')->nullable();
            $table->json('applicable_taxes')->nullable();

            $table->float('freight_charge')->nullable();
            $table->float('bill_charge')->nullable();
            $table->float('package_charge')->nullable();
            $table->float('departure_1_total')->nullable();

            $table->float('destination_handling_charge')->nullable();
            $table->float('destination_slpa_charge')->nullable();
            $table->float('destination_bond_charge')->nullable();
            $table->float('destination_1_total')->nullable();
            $table->float('destination_1_tax')->nullable();
            $table->float('destination_1_total_with_tax')->nullable();

            $table->float('destination_do_charge')->nullable();
            $table->float('destination_demurrage_charge')->nullable();
            $table->float('destination_stamp_charge')->nullable();
            $table->float('destination_other_charge')->nullable();
            $table->float('destination_2_total')->nullable();
            $table->float('destination_2_tax')->nullable();
            $table->float('destination_2_total_with_tax')->nullable();

            // departure_total_charge
            // if prepaid departure_1_total + destination_1_total_with_tax(base currency)
            // if postpaid departure_1_total
            $table->float('departure_total_charge')->nullable();
            $table->float('departure_discount')->nullable();
            $table->float('departure_additional_charge')->nullable();
            $table->float('departure_net_total')->nullable();
            $table->float('departure_paid_amount')->nullable();
            $table->float('departure_due')->nullable();

            // destination_total_charge
            // if prepaid destination_2_total
            // if postpaid destination_1_total + destination_2_total
            $table->float('destination_total_charge')->nullable();
            $table->float('destination_discount')->nullable();
            $table->float('destination_additional_charge')->nullable();

            // destination_total_tax
            // destination_total_charge + destination_additional_charge - destination_discount
            $table->float('destination_total_tax')->nullable();

            // destination_net_total
            // destination_total_charge - destination_discount + destination_additional_charge + destination_total_tax
            $table->float('destination_net_total')->nullable();
            $table->float('destination_paid_amount')->nullable();
            $table->float('destination_due')->nullable();

            // grand_total
            // if departure_net_total  + destination_net_total
            $table->float('grand_total')->nullable()->comment('Total charge for the HBL including all charges and discounts');
            $table->float('grand_total_paid')->nullable()->comment('Total amount paid for the HBL');
            $table->float('grand_total_due')->nullable()->comment('Total amount due for the HBL');

            $table->foreign('hbl_id')->references('id')->on('hbl')->onDelete('cascade')->comment('Foreign key constraint linking to the HBL table');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade')->comment('Foreign key constraint linking to the branches table');
            $table->index(['hbl_id', 'branch_id'], 'hbl_charges_hbl_branch_index')->comment('Index for quick lookup of HBL charges by HBL ID and branch ID');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hbl_charges');
    }
};
