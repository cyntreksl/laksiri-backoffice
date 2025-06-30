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
        Schema::create('hbl_destination_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hbl_id')->comment('Foreign key to the HBL table');
            $table->unsignedBigInteger('branch_id');
            $table->char('base_currency_code', 3);
            $table->float('base_currency_rate_in_lkr')->default(1.0);
            $table->boolean('is_branch_prepaid')->nullable();
            $table->json('applicable_taxes')->nullable();

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

            $table->timestamp('stop_demurrage_at')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hbl_destination_charges');
    }
};
