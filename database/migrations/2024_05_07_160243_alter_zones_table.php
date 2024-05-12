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
        Schema::table('zones', function (Blueprint $table) {
            $table->renameColumn('zone_name', 'name');
            $table->foreignIdFor(\App\Models\Branch::class)->after('pickup_areas')->cascadeOnDelete();
            $table->string('pickup_areas')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->renameColumn('name', 'zone_name');
            $table->dropColumn('branch_id');
            $table->string('pickup_areas')->nullable(false)->change();
        });
    }
};
