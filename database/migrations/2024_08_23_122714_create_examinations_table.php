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
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('released_by');
            $table->unsignedBigInteger('customer_queue_id');
            $table->unsignedBigInteger('token_id');
            $table->unsignedBigInteger('hbl_id');
            $table->unsignedBigInteger('package_queue_id');
            $table->json('released_packages')->nullable();
            $table->timestamp('released_at')
                ->nullable();
            $table->boolean('is_issued_gate_pass')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
