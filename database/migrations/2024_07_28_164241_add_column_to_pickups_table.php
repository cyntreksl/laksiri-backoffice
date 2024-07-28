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
            $table->integer('retry_attempts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->dropColumn('retry_attempts');
        });
    }
};
