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
        Schema::create('sl_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hbl_id');
            $table->time('clearing_time');
            $table->date('date');
            $table->unsignedBigInteger('container_id')->nullable();
            $table->decimal('grand_volume', 10, 2)->default(0);
            $table->decimal('grand_weight', 10, 2)->default(0);
            $table->decimal('port_charge_rate', 15, 2)->default(0);
            $table->decimal('port_charge_amount', 15, 2)->default(0);
            $table->decimal('handling_charge_rate', 15, 2)->default(0);
            $table->decimal('handling_charge_amount', 15, 2)->default(0);
            $table->decimal('storage_charge_rate', 15, 2)->default(0);
            $table->decimal('storage_charge_amount', 15, 2)->default(0);
            $table->decimal('dmg_charge_rate', 15, 2)->default(0);
            $table->decimal('dmg_charge_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('do_charge', 15, 2)->default(0);
            $table->decimal('stamp_charge', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sl_invoices');
    }
};
