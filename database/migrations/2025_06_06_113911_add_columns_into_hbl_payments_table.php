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
        Schema::table('hbl_payments', function (Blueprint $table) {
            $table->after('hbl_id', function (Blueprint $table) {
                $table->string('base_currency')
                    ->nullable();
                $table->float('currency_rate')
                    ->nullable();
                $table->char('currency_code')
                    ->nullable();
                $table->float('freight_charge')
                    ->default(0)
                    ->nullable();
                $table->float('bill_charge')
                    ->default(0)
                    ->nullable();
                $table->float('other_charge')
                    ->default(0)
                    ->nullable();
                $table->float('discount')
                    ->default(0)
                    ->nullable();
                $table->double('additional_charge', 10, 2)
                    ->default(0)
                    ->nullable();
                $table->double('do_charge', 10, 2)
                    ->default(0)
                    ->nullable();
                $table->float('destination_charge')
                    ->default(0)
                    ->nullable();
                $table->boolean('is_departure_charges_paid')
                    ->default(false);
                $table->boolean('is_destination_charges_paid')
                    ->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl_payments', function (Blueprint $table) {
            $table->dropColumn(['base_currency', 'currency_rate', 'currency_code', 'freight_charge', 'bill_charge', 'other_charge', 'discount', 'additional_charge', 'do_charge', 'destination_charge', 'is_departure_charges_paid', 'is_destination_charges_paid']);
        });
    }
};
