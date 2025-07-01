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
        Schema::create('branch_destination_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->float('sea_cargo_port_charge')->nullable();
            $table->float('sea_cargo_handling_charge')->nullable();
            $table->float('sea_cargo_bond_charge')->nullable();
            $table->float('sea_cargo_slpa_charge')->nullable();
            $table->string('sea_cargo_reimbursement_logic')->nullable();
            $table->float('air_cargo_port_charge')->nullable();
            $table->float('air_cargo_handling_charge')->nullable();
            $table->float('air_cargo_bond_charge')->nullable();
            $table->float('air_cargo_slpa_charge')->nullable();
            $table->string('air_cargo_reimbursement_logic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_destination_prices');
    }
};
