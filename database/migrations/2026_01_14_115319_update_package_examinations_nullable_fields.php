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
        Schema::table('package_examinations', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['examination_id']);
            $table->dropForeign(['customer_queue_id']);
            
            // Modify columns to be nullable
            $table->unsignedBigInteger('examination_id')->nullable()->change();
            $table->unsignedBigInteger('customer_queue_id')->nullable()->change();
            
            // Re-add foreign keys with set null on delete
            $table->foreign('examination_id')->references('id')->on('examinations')->onDelete('set null');
            $table->foreign('customer_queue_id')->references('id')->on('customer_queues')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_examinations', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['examination_id']);
            $table->dropForeign(['customer_queue_id']);
            
            // Revert columns to not nullable
            $table->unsignedBigInteger('examination_id')->nullable(false)->change();
            $table->unsignedBigInteger('customer_queue_id')->nullable(false)->change();
            
            // Re-add foreign keys with cascade
            $table->foreign('examination_id')->references('id')->on('examinations')->onDelete('cascade');
            $table->foreign('customer_queue_id')->references('id')->on('customer_queues')->onDelete('cascade');
        });
    }
};
