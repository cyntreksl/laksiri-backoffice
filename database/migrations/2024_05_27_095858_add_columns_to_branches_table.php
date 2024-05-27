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
        Schema::table('branches', function (Blueprint $table) {
            $table->after('type', function (Blueprint $table) {
                $table->string('currency_name')->nullable();
                $table->char('currency_symbol', 3)->nullable();
                $table->json('cargo_modes')->nullable();
                $table->json('delivery_types')->nullable();
                $table->json('package_types')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'currency_name',
                'currency_symbol',
                'cargo_modes',
                'delivery_types',
                'package_types',
            ]);
        });
    }
};
