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
        Schema::create('hbl_packages', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->integer('hbl_id');
            $table->string('package_type');
            $table->float('length');
            $table->float('width');
            $table->float('height');
            $table->integer('quantity');
            $table->float('weight')->nullable();
            $table->float('volume')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hbl_packages');
    }
};
