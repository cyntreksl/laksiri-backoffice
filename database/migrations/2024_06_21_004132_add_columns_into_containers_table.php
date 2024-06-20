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
        Schema::table('containers', function (Blueprint $table) {
            $table->after('unloading_ended_by', function (Blueprint $table) {
                $table->string('note')->nullable();
                $table->boolean('is_reached')->default(false);
                $table->date('reached_date')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn(['note', 'is_reached', 'reached_date']);
        });
    }
};
