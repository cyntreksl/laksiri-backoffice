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
        Schema::create('queue_logs', function (Blueprint $table) {
            $table->id();
            $table->string('queueable_type');
            $table->unsignedBigInteger('queueable_id');
            $table->unsignedBigInteger('auth_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('token_id');
            $table->string('queue_type');
            $table->timestamp('arrival_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_logs');
    }
};
