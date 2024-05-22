<?php

use App\Enum\HBLPaymentStatus;
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
        Schema::create('hbl_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnDelete();
            $table->foreignId('hbl_id')
                ->constrained('hbl')
                ->cascadeOnDelete();
            $table->double('grand_total')
                ->default(0);
            $table->double('paid_amount')
                ->default(0);
            $table->enum('status', [
                HBLPaymentStatus::FULL_PAID->value,
                HBLPaymentStatus::PARTIAL_PAID->value,
                HBLPaymentStatus::NOT_PAID->value,
            ]);
            $table->unsignedBigInteger('created_by')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_b_l_payments');
    }
};
