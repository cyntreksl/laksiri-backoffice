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
        Schema::table('couriers', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0.00)->after('consignee_note');
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('amount')->comment('calculated discount value');
            $table->decimal('tax_amount', 10, 2)->default(0.00)->after('discount_amount')->comment('calculated tax value');
            $table->decimal('grand_total', 10, 2)->default(0.00)->after('tax_amount')->comment('total amount after tax and discount');

            $table->string('tax_method')->nullable()->after('grand_total')->comment('by percentage or fixed amount');
            $table->decimal('tax_value')->nullable()->after('tax_method')->comment('user defined tax value');

            $table->string('discount_method')->nullable()->after('tax_value')->comment('by percentage or fixed amount');
            $table->decimal('discount_value')->nullable()->after('discount_method')->comment('user defined tax value');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropColumn(['amount', 'discount_amount', 'tax_amount', 'grand_total', 'tax_method', 'tax_value', 'discount_method', 'discount_value']);
        });
    }
};
