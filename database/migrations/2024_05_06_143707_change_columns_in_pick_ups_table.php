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
            $table->string('email')
                ->nullable()
                ->change();
            $table->date('pickup_date')
                ->nullable()
                ->after('hbl_id');
            $table->time('pickup_time_start')
                ->nullable()
                ->after('pickup_date');
            $table->time('pickup_time_end')
                ->nullable()
                ->after('pickup_time_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->string('email')
                ->change();
            $table->dropColumn(['pickup_date', 'pickup_time_start', 'pickup_time_end']);
        });
    }
};
