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
        Schema::table('hbl', function (Blueprint $table) {
            $table->string('reference', 20)
                ->nullable()
                ->change();
            $table->string('hbl', 256)
                ->nullable()
                ->change();
            $table->string('hbl_name', 256)
                ->nullable()
                ->change();
            $table->float('freight_charge')
                ->nullable()
                ->default(0)
                ->change();
            $table->float('bill_charge')
                ->nullable()
                ->default(0)
                ->change();
            $table->float('other_charge')
                ->nullable()
                ->default(0)
                ->change();
            $table->float('paid_amount')
                ->nullable()
                ->default(0)
                ->change();
            $table->float('grand_total')
                ->nullable()
                ->default(0)
                ->change();
            $table->float('discount')
                ->nullable()
                ->default(0)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->string('reference', 20)
                ->change();
            $table->string('hbl', 256)
                ->change();
            $table->string('hbl_name', 256)
                ->change();
            $table->float('freight_charge')
                ->default(0)
                ->change();
            $table->float('bill_charge')
                ->default(0)
                ->change();
            $table->float('other_charge')
                ->default(0)
                ->change();
            $table->float('paid_amount')
                ->default(0)
                ->change();
            $table->float('grand_total')
                ->default(0)
                ->change();
            $table->float('discount')
                ->default(0)
                ->change();
        });
    }
};
