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
            $table->timestamp('finance_approved_date')->nullable()->after('finance_approved_by');
            $table->boolean('is_refund_collected')->default(false)->after('finance_approved_date');
            $table->bigInteger('refund_collected_by')->nullable()->after('is_refund_collected');
            $table->timestamp('refund_collected_date')->nullable()->after('refund_collected_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('container_payments', function (Blueprint $table) {
            $table->dropColumn('finance_approved_date');
            $table->dropColumn('is_refund_collected');
            $table->dropColumn('refund_collected_by');
            $table->dropColumn('refund_collected_date');
        });
    }
};
