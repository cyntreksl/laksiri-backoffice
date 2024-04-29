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
        Schema::create('h_b_l_s', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20);
            $table->integer('agent_id');
            $table->enum('cargo_type', [CargoType::SEA_CARGO->value, CargoType::AIR_CARGO->value])->nullable();
            $table->enum('hbl_type', [HBLType::UBP->value, HBLType::GIFT->value, HBLType::DOOR_TO_DOOR->value])->nullable();
            $table->string('hbl', 256);
            $table->string('hbl_name', 256);
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
            $table->string('warehouse')->nullable();
            $table->float('freight_charge')->default(0);
            $table->float('bill_charge')->default(0);
            $table->float('other_charge')->default(0);
            $table->float('discount')->default(0);
            $table->float('paid_amount')->default(0);
            $table->float('grand_total')->default(0);
            $table->string('status')->nullable();
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
        Schema::dropIfExists('h_b_l_s');
    }
};
