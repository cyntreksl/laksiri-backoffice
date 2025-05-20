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
        Schema::table('container_payments', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('finance_approved_date');
            $table->bigInteger('paid_by')->nullable()->after('is_paid');
            $table->string('payment_received_by')->nullable()->after('paid_by');
            $table->timestamp('paid_date')->nullable()->after('payment_received_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('container_payments', function (Blueprint $table) {
            $table->dropColumn('is_paid');
            $table->dropColumn('paid_by');
            $table->dropColumn('payment_received_by');
            $table->dropColumn('paid_date');
        });
    }
};
