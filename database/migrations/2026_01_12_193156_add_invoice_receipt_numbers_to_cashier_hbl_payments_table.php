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
            $table->string('invoice_number')->unique()->nullable()->after('paid_amount');
            $table->string('receipt_number')->unique()->nullable()->after('invoice_number');
            
            $table->index('invoice_number');
            $table->index('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_hbl_payments', function (Blueprint $table) {
            $table->dropIndex(['invoice_number']);
            $table->dropIndex(['receipt_number']);
            $table->dropColumn(['invoice_number', 'receipt_number']);
        });
    }
};
