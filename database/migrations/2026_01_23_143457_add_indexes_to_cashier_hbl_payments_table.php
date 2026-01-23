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
        Schema::table('cashier_hbl_payments', function (Blueprint $table) {
            // Add indexes for better query performance
            $table->index('hbl_id');
            $table->index('created_at');
            $table->index(['hbl_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_hbl_payments', function (Blueprint $table) {
            $table->dropIndex(['hbl_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['hbl_id', 'created_at']);
        });
    }
};
