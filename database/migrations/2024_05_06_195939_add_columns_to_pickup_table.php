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
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->boolean('is_urgent_pickup')->default(false)->after('pickup_time_end');
            $table->boolean('is_from_important_customer')->default(false)->after('is_urgent_pickup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {
           $table->dropColumn('is_urgent_pickup');
           $table->dropColumn('is_from_important_customer');
        });
    }
};
