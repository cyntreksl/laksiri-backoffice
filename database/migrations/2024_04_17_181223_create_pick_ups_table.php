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
        Schema::create('pick_ups', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20);
            $table->integer('agent_id');
            $table->string('cargo_type', 20)->nullable();
            $table->string('name', 256);
            $table->string('email');
            $table->string('contact_number', 20);
            $table->text('address');
            $table->text('location_name')->nullable();
            $table->float('location_longitude')->nullable();
            $table->float('location_latitude')->nullable();
            $table->integer('zone_id')->nullable();
            $table->text('notes')->nullable();
            $table->integer('driver_id')->nullable();
            $table->timestamp('driver_assigned_at')->nullable();
            $table->integer('hbl_id')->nullable();
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pick_ups');
    }
};
