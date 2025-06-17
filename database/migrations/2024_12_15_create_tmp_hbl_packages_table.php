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
        Schema::create('tmp_hbl_packages', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // To track the upload session
            $table->string('hbl_number'); // Reference to the HBL number instead of ID
            $table->string('package_type');
            $table->string('measure_type', 10)->nullable();
            $table->double('length');
            $table->double('width');
            $table->double('height');
            $table->integer('quantity');
            $table->double('volume')->nullable();
            $table->double('weight')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmp_hbl_packages');
    }
};
