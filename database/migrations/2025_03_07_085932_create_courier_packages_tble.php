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
        Schema::create('courier_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('courier_id');
            $table->string('package_type');
            $table->double('length');
            $table->double('width');
            $table->double('height');
            $table->integer('quantity');
            $table->double('weight');
            $table->double('volume')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->string('measure_type', 10);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_packages_tble');
    }
};
