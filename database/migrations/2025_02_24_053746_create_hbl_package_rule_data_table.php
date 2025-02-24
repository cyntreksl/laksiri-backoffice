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
        Schema::create('hbl_package_rule_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->boolean('is_package_rule')->default(false);
            $table->json('rules')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hbl_package_rule_data');
    }
};
