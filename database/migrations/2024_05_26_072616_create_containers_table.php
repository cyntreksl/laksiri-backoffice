<?php

use App\Enum\CargoType;
use App\Enum\ContainerType;
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
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->enum('cargo_type', [CargoType::SEA_CARGO->value, CargoType::AIR_CARGO->value])->nullable();
            $table->enum('container_type', [ContainerType::TwentyFTGeneral->value, ContainerType::TwentyFTHighCube->value, ContainerType::FortyFTGeneral->value, ContainerType::FortyFTHighCube->value])->nullable();
            $table->string('reference')->nullable();
            $table->string('bl_number')->nullable(); // For Sea Cargo
            $table->string('awb_number')->nullable(); // For Air Cargo
            $table->string('container_number')->nullable();
            $table->string('seal_number')->nullable();
            $table->float('maximum_volume')->nullable();
            $table->float('minimum_volume')->nullable();
            $table->float('maximum_weight')->nullable();
            $table->float('minimum_weight')->nullable();
            $table->float('maximum_volumetric_weight')->nullable();
            $table->float('minimum_volumetric_weight')->nullable();
            $table->date('estimated_time_of_departure')->nullable();
            $table->date('estimated_time_of_arrival')->nullable();
            $table->string('vessel_name')->nullable(); // For Sea Cargo
            $table->string('voyage_number')->nullable(); // For Sea Cargo
            $table->string('shipping_line')->nullable(); // For Sea Cargo
            $table->string('port_of_loading')->nullable(); // For Sea Cargo
            $table->string('port_of_discharge')->nullable(); // For Sea Cargo
            $table->string('flight_number')->nullable(); // For Air Cargo
            $table->string('airline_name')->nullable(); // For Air Cargo
            $table->string('airport_of_departure')->nullable(); // For Air Cargo
            $table->string('airport_of_arrival')->nullable(); // For Air Cargo
            $table->string('cargo_class')->nullable(); // For Air Cargo
            $table->string('status')->default('Container Ordered');
            $table->float('system_status')->default(0); // For Air Cargo
            $table->timestamp('loading_started_at')->nullable();
            $table->timestamp('loading_ended_at')->nullable();
            $table->timestamp('unloading_started_at')->nullable();
            $table->timestamp('unloading_ended_at')->nullable();
            $table->integer('loading_started_by')->nullable();
            $table->integer('loading_ended_by')->nullable();
            $table->integer('unloading_started_by')->nullable();
            $table->integer('unloading_ended_by')->nullable();
            $table->integer('created_by');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
