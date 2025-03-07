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
            $table->string('additional_mobile_number')->nullable()->after('contact_number');
            $table->string('whatsapp_number')->nullable()->after('additional_mobile_number');
            $table->string('consignee_additional_mobile_number')->nullable()->after('consignee_contact');
            $table->string('consignee_whatsapp_number')->nullable()->after('consignee_additional_mobile_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropColumn('additional_mobile_number');
            $table->dropColumn('whatsapp_number');
            $table->dropColumn('consignee_additional_mobile_number');
            $table->dropColumn('consignee_whatsapp_number');
        });
    }
};
