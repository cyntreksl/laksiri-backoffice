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
        Schema::create('hbl_departure_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hbl_id')->comment('Foreign key to the HBL table');
            $table->unsignedBigInteger('branch_id');
            $table->char('base_currency_code', 3);
            $table->float('base_currency_rate_in_lkr')->default(1.0);
            $table->boolean('is_branch_prepaid')->nullable();

            $table->float('freight_charge')->nullable();
            $table->float('bill_charge')->nullable();
            $table->float('package_charge')->nullable();
            $table->float('discount')->nullable();
            $table->float('additional_charges')->nullable();
            $table->float('departure_grand_total')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hbl_departure_charges');
    }
};
