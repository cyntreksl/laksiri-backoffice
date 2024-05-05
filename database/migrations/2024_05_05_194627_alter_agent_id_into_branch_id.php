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
        Schema::rename('h_b_l_s', 'hbl');
        Schema::table('hbl', function (Blueprint $table) {
            $table->renameColumn('agent_id', 'branch_id');
        });
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->renameColumn('agent_id', 'branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('hbl', 'h_b_l_s');
        Schema::table('hbl', function (Blueprint $table) {
            $table->renameColumn('branch_id', 'agent_id');
        });
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->renameColumn('branch_id', 'agent_id');
        });
    }
};
