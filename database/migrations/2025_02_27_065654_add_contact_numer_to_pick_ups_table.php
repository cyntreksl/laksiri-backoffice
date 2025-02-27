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
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->string('additional_mobile_number')->nullable()->after('contact_number');
            $table->string('whatsapp_number')->nullable()->after('additional_mobile_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->dropColumn('additional_mobile_number');
            $table->dropColumn('whatsapp_number');
        });
    }
};
