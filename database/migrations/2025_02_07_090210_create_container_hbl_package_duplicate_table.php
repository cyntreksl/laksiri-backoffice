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
        Schema::create('duplicate_container_hbl_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')
                ->constrained('containers')
                ->cascadeOnDelete();
            $table->foreignId('hbl_package_id')
                ->constrained('hbl_packages')
                ->cascadeOnDelete();
            $table->enum('status', ['draft', 'loaded', 'draft-unload', 'unloaded'])
                ->default('draft');
            $table->unsignedBigInteger('loaded_by');
            $table->unsignedBigInteger('unloaded_by')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duplicate_container_hbl_package_duplicate');
    }
};
