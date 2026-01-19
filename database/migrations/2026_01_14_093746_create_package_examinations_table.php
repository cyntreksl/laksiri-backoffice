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
        Schema::create('package_examinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hbl_package_id');
            $table->unsignedBigInteger('examination_id')->nullable();
            $table->unsignedBigInteger('customer_queue_id')->nullable();
            $table->unsignedBigInteger('token_id');
            $table->enum('action', ['released', 'held', 'returned_to_bond']);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('processed_by');
            $table->timestamp('processed_at');
            $table->timestamps();
            
            $table->foreign('hbl_package_id')->references('id')->on('hbl_packages')->onDelete('cascade');
            $table->foreign('examination_id')->references('id')->on('examinations')->onDelete('set null');
            $table->foreign('customer_queue_id')->references('id')->on('customer_queues')->onDelete('set null');
            $table->foreign('token_id')->references('id')->on('tokens')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_examinations');
    }
};
