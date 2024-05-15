<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enum\PickupStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->foreignId('pickup_id')->nullable()->after('branch_id')->constrained('pick_ups');
        });

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
                ->after('pickup_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropForeign(['pickup_id']);
            $table->dropColumn('pickup_id');
        });

        Schema::table('pick_ups', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
