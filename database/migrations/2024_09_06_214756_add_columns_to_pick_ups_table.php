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
            $table->after('status', function (Blueprint $table) {
                $table->string('hbl_number')->nullable();
                $table->string('cr_number')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->dropColumn(['hbl_number', 'cr_number']);
        });
    }
};
