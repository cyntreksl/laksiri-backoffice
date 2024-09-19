<?php

use App\Enum\PickupStatus;
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
            $table->enum('status', [
                PickupStatus::ACCEPTED->value,
                PickupStatus::COLLECTED->value,
                PickupStatus::REJECTED->value,
                PickupStatus::COMPLETED->value,
                PickupStatus::CANCELLED->value,
                PickupStatus::PENDING->value,
                PickupStatus::PROCESSING->value,
            ])
                ->default(PickupStatus::PENDING->value)
                ->after('pickup_order')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pick_ups', function (Blueprint $table) {
            $table->enum('status', [
                PickupStatus::ACCEPTED->value,
                PickupStatus::COLLECTED->value,
                PickupStatus::REJECTED->value,
                PickupStatus::COMPLETED->value,
                PickupStatus::CANCELLED->value,
                PickupStatus::PENDING->value,
            ])
                ->default(PickupStatus::PENDING->value)
                ->after('pickup_order')
                ->change();
        });
    }
};
