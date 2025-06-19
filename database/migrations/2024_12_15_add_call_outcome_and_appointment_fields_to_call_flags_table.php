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
            $table->string('call_outcome')->default('contacted')->after('notes');
            $table->date('appointment_date')->nullable()->after('followup_date');
            $table->text('appointment_notes')->nullable()->after('appointment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_flags', function (Blueprint $table) {
            $table->dropColumn(['call_outcome', 'appointment_date', 'appointment_notes']);
        });
    }
};
