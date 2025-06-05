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
        Schema::table('vessel_schedule', function (Blueprint $table) {
            $table->string('status')
                ->after('end_date')
                ->default('SYSTEM_GENERATED')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vessel_schedule', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
