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
        Schema::create('token_cancellations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('token_id');
            $table->unsignedBigInteger('cancelled_by');
            $table->text('cancellation_reason');
            $table->string('token_status_at_cancellation', 50);
            $table->boolean('invoice_cancelled')->default(false);
            $table->boolean('gate_pass_cancelled')->default(false);
            $table->json('hbl_package_status');
            $table->json('warnings_shown')->nullable();
            $table->json('post_cancellation_impacts')->nullable();
            $table->timestamps();
            
            $table->foreign('token_id')->references('id')->on('tokens')->onDelete('cascade');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('restrict');
            
            $table->index('token_id');
            $table->index('cancelled_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_cancellations');
    }
};
