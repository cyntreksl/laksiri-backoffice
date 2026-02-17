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
        Schema::table('call_flags', function (Blueprint $table) {
            $table->boolean('override_validation')->default(false)->after('appointment_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_flags', function (Blueprint $table) {
            $table->dropColumn('override_validation');
        });
    }
};
