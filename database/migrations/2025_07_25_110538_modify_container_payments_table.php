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
            $table->after('do_charge', function (Blueprint $table) {
                $table->boolean('do_charge_finance_approved')->default(false);
                $table->timestamp('do_charge_requested_at')->nullable();
                $table->timestamp('do_charge_approved_at')->nullable();
                $table->unsignedBigInteger('do_charge_requested_by')->nullable();
                $table->unsignedBigInteger('do_charge_approved_by')->nullable();
            });

            $table->after('demurrage_charge', function (Blueprint $table) {
                $table->boolean('demurrage_charge_finance_approved')->default(false);
                $table->timestamp('demurrage_charge_requested_at')->nullable();
                $table->timestamp('demurrage_charge_approved_at')->nullable();
                $table->unsignedBigInteger('demurrage_charge_requested_by')->nullable();
                $table->unsignedBigInteger('demurrage_charge_approved_by')->nullable();
            });

            $table->after('assessment_charge', function (Blueprint $table) {
                $table->boolean('assessment_charge_finance_approved')->default(false);
                $table->timestamp('assessment_charge_requested_at')->nullable();
                $table->timestamp('assessment_charge_approved_at')->nullable();
                $table->unsignedBigInteger('assessment_charge_requested_by')->nullable();
                $table->unsignedBigInteger('assessment_charge_approved_by')->nullable();
            });

            $table->after('slpa_charge', function (Blueprint $table) {
                $table->boolean('slpa_charge_finance_approved')->default(false);
                $table->timestamp('slpa_charge_requested_at')->nullable();
                $table->timestamp('slpa_charge_approved_at')->nullable();
                $table->unsignedBigInteger('slpa_charge_requested_by')->nullable();
                $table->unsignedBigInteger('slpa_charge_approved_by')->nullable();
            });

            $table->after('refund_charge', function (Blueprint $table) {
                $table->boolean('refund_charge_finance_approved')->default(false);
                $table->timestamp('refund_charge_requested_at')->nullable();
                $table->timestamp('refund_charge_approved_at')->nullable();
                $table->unsignedBigInteger('refund_charge_requested_by')->nullable();
                $table->unsignedBigInteger('refund_charge_approved_by')->nullable();
            });

            $table->after('clearance_charge', function (Blueprint $table) {
                $table->boolean('clearance_charge_finance_approved')->default(false);
                $table->timestamp('clearance_charge_requested_at')->nullable();
                $table->timestamp('clearance_charge_approved_at')->nullable();
                $table->unsignedBigInteger('clearance_charge_requested_by')->nullable();
                $table->unsignedBigInteger('clearance_charge_approved_by')->nullable();
            });

            $table->dropColumn([
                'is_finance_approved',
                'finance_approved_by',
                'finance_approved_date',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('container_payments', function (Blueprint $table) {
            $table->dropColumn([
                'do_charge_finance_approved',
                'do_charge_requested_at',
                'do_charge_approved_at',
                'do_charge_requested_by',
                'do_charge_approved_by',

                'demurrage_charge_finance_approved',
                'demurrage_charge_requested_at',
                'demurrage_charge_approved_at',
                'demurrage_charge_requested_by',
                'demurrage_charge_approved_by',

                'assessment_charge_finance_approved',
                'assessment_charge_requested_at',
                'assessment_charge_approved_at',
                'assessment_charge_requested_by',
                'assessment_charge_approved_by',

                'slpa_charge_finance_approved',
                'slpa_charge_requested_at',
                'slpa_charge_approved_at',
                'slpa_charge_requested_by',
                'slpa_charge_approved_by',

                'refund_charge_finance_approved',
                'refund_charge_requested_at',
                'refund_charge_approved_at',
                'refund_charge_requested_by',
                'refund_charge_approved_by',

                'clearance_charge_finance_approved',
                'clearance_charge_requested_at',
                'clearance_charge_approved_at',
                'clearance_charge_requested_by',
                'clearance_charge_approved_by',
            ]);

            $table->boolean('is_finance_approved')->default(false);
            $table->bigInteger('finance_approved_by')->nullable();
            $table->timestamp('finance_approved_date')->nullable();
        });
    }
};
