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
        Schema::create('cashier_hbl_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('verified_by');
            $table->unsignedBigInteger('customer_queue_id');
            $table->unsignedBigInteger('token_id');
            $table->unsignedBigInteger('hbl_id');
            $table->float('paid_amount');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_hbl_payments');
    }
};
