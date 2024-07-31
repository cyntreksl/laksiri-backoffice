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
            $table->after('id', function ($table) {
                $table->unsignedBigInteger('shipper_id')->nullable();
                $table->unsignedBigInteger('consignee_id')->nullable();
                $table->foreign('shipper_id')->references('id')->on('users');
                $table->foreign('consignee_id')->references('id')->on('users');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->dropForeign('pick_ups_shipper_id_foreign');
            $table->dropForeign('pick_ups_consignee_id_foreign');
            $table->dropColumn('shipper_id');
            $table->dropColumn('consignee_id');
        });
    }
};
