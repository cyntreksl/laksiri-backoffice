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
        Schema::create('pickup_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_id')->constrained('pick_ups')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('reference', 20);
            $table->string('name', 256);
            $table->text('picker_note')->nullable();
            $table->text('address')->nullable();
            $table->date('pickup_date')->nullable();
            $table->string('auth')->nullable();
            $table->timestamp('driver_assigned_at')->nullable();
            $table->double('system_status')->default(1.1);
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
        Schema::dropIfExists('pickup_exceptions');
    }
};
