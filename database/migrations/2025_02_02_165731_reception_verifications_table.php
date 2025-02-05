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
        Schema::create('reception_verifications', function (Blueprint $table) {
            $table->id();
            $table->json('is_checked')->nullable();
            $table->unsignedBigInteger('verified_by');
            $table->unsignedBigInteger('customer_queue_id');
            $table->unsignedBigInteger('token_id');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reception_verifications');
    }
};
