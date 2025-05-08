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
        Schema::create('container_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('container_id');
            $table->double('do_charge')->default(0);
            $table->double('demurrage_charge')->default(0);
            $table->double('assessment_charge')->default(0);
            $table->double('slpa_charge')->default(0);
            $table->double('refund_charge')->default(0);
            $table->double('clearance_charge')->default(0);
            $table->double('total')->default(0);
            $table->boolean('is_finance_approved')->default(false);
            $table->bigInteger('finance_approved_by')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_payments');
    }
};
