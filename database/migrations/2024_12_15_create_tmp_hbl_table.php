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
        Schema::create('tmp_hbl', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // To track the upload session
            $table->string('hbl_number')->nullable();
            $table->string('hbl_name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('additional_mobile_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('nic')->nullable();
            $table->string('iq_number')->nullable();
            $table->text('address')->nullable();
            $table->string('consignee_name')->nullable();
            $table->string('consignee_nic')->nullable();
            $table->string('consignee_contact')->nullable();
            $table->string('consignee_additional_mobile_number')->nullable();
            $table->string('consignee_whatsapp_number')->nullable();
            $table->text('consignee_address')->nullable();
            $table->text('consignee_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmp_hbl');
    }
};
