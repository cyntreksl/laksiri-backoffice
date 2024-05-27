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
        Schema::table('branch_prices', function (Blueprint $table) {
            $table->after('condition', function (Blueprint $table) {
                $table->float('bill_price')
                    ->nullable();
                $table->float('bill_vat')
                    ->comment('must be percentage value')
                    ->nullable();
                $table->string('destination_charges')
                    ->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_prices', function (Blueprint $table) {
            $table->dropColumn(['bill_price', 'bill_vat', 'destination_charges']);
        });
    }
};
