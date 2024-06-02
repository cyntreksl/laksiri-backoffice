<?php

use App\Enum\CargoType;
use App\Enum\HBLType;
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
        Schema::create('loaded_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();
            $table->foreignId('container_id')
                ->constrained('containers')
                ->cascadeOnDelete();
            $table->foreignId('hbl_id')
                ->constrained('hbl')
                ->cascadeOnDelete();
            $table->foreignId('hbl_package_id')
                ->constrained('hbl_packages')
                ->cascadeOnDelete();
            $table->boolean('is_draft')
                ->default(false);
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->enum('cargo_type', [CargoType::SEA_CARGO->value, CargoType::AIR_CARGO->value])->nullable();
            $table->enum('delivery_type', [HBLType::GIFT->value, HBLType::UBP->value, HBLType::DOOR_TO_DOOR->value])->nullable();
            $table->integer('total_packages')->nullable();
            $table->double('total_weight')->nullable();
            $table->double('total_volume')->nullable();
            $table->integer('total_hbl')->nullable();
            $table->string('status')->nullable();
            // must be added destination
            $table->unsignedBigInteger('loaded_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loaded_containers');
    }
};
