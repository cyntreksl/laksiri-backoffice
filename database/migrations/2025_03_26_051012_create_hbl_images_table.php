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
        Schema::create('hbl_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->nullable()->constrained('officers')->onDelete('cascade');
            $table->foreignId('hbl_id')->nullable()->constrained('hbl')->onDelete('cascade');
            $table->foreignId('hbl_packages_id')->nullable()->constrained('hbl_packages')->onDelete('cascade');
            $table->enum('image_type', ['shipper_nic', 'shipper_passport', 'package']);

            $table->string('image_path');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hbl_images');
    }
};
