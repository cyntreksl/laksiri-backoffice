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
        Schema::create('branch_package_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id')->comment('origin_branch_id');
            $table->integer('destination_branch_id');
            $table->string('cargo_mode');
            $table->string('hbl_type');
            $table->string('rule_title');
            $table->float('length')->nullable();
            $table->float('width')->nullable();
            $table->float('height')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_package_prices');
    }
};
