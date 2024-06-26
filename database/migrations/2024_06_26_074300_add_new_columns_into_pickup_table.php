<?php

use App\Enum\PickupType;
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
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->enum('pickup_type', [PickupType::URGENT_PICKUP->value, PickupType::VIP_CUSTOMER->value, PickupType::NEED_TROLLY->value])->nullable();
            $table->text('pickup_note')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {

        });
    }
};
