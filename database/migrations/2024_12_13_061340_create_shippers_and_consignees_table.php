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
        Schema::create('shippers_and_consignees', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile_number');
            $table->string('pp_or_nic_no')->nullable();
            $table->string('residency_no')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippers_and_consignees');
    }
};
