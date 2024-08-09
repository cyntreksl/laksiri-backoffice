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
        if (Schema::hasColumn('pick_ups', 'is_urgent_pickup')) {
            Schema::table('pick_ups', function (Blueprint $table) {
                $table->dropColumn('is_urgent_pickup');
            });
        }

        if (Schema::hasColumn('pick_ups', 'is_from_important_customer')) {
            Schema::table('pick_ups', function (Blueprint $table) {
                $table->dropColumn('is_from_important_customer');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->boolean('is_urgent_pickup')->default(false)->after('pickup_time_end');
            $table->boolean('is_from_important_customer')->default(false)->after('is_urgent_pickup');
        });
    }
};
