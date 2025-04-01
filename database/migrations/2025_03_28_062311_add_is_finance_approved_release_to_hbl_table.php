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
            $table->boolean('is_finance_release_approved')
                ->default(false)
                ->after('cr_number');

            $table->bigInteger('finance_release_approved_by')
                ->nullable()
                ->after('is_finance_release_approved');

            $table->date('finance_release_approved_date')
                ->nullable()
                ->after('finance_release_approved_by'); // Corrected placement
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropColumn([
                'is_finance_release_approved',
                'finance_release_approved_by',
                'finance_release_approved_date',
            ]);
        });
    }
};
