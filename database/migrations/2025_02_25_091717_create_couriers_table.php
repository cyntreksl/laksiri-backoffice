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
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('courier_number');
            $table->enum('cargo_type', [CargoType::SEA_CARGO->value, CargoType::AIR_CARGO->value])->nullable();
            $table->enum('hbl_type', [HBLType::UPB->value, HBLType::GIFT->value])->nullable();
            $table->unsignedBigInteger('courier_agent');
            $table->string('name', 256);
            $table->string('email')->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('nic')->nullable();
            $table->string('iq_number')->nullable();
            $table->text('address')->nullable();
            $table->string('consignee_name', 256)->nullable();
            $table->string('consignee_nic')->nullable();
            $table->string('consignee_contact', 20)->nullable();
            $table->text('consignee_address')->nullable();
            $table->text('consignee_note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('status', 255)->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
