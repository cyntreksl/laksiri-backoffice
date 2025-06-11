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
        Schema::table('hbl_packages', function (Blueprint $table) {
            $table->after('measure_type', function (Blueprint $table) {
                $table->timestamp('loaded_at')->nullable();
                $table->timestamp('unloaded_at')->nullable();
                $table->integer('loaded_by')->nullable();
                $table->integer('unloaded_by')->nullable();
                $table->string('airport_of_departure')->nullable();
                $table->string('airport_of_arrival')->nullable();
                $table->boolean('is_hold')->default(false);
                $table->boolean('is_rtf')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl_packages', function (Blueprint $table) {
            $table->dropColumn(['loaded_at', 'unloaded_at', 'loaded_by', 'unloaded_by', 'airport_of_departure', 'airport_of_arrival', 'is_hold', 'is_rtf']);
        });
    }
};
