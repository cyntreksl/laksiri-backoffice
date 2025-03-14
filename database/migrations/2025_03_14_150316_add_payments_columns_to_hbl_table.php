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
            $table->boolean('is_departure_charges_paid')->default(false)->after('do_charge');
            $table->boolean('is_destination_charges_paid')->default(false)->after('is_departure_charges_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropColumn('is_departure_charges_paid');
            $table->dropColumn('is_destination_charges_paid');
        });
    }
};
