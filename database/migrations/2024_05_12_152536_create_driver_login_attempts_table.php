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
        Schema::create('driver_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('users', 'id')
                ->onDelete('cascade');
            $table->timestamp('time')->nullable();
            $table->double('longitude', 11, 8)->nullable();
            $table->double('latitude', 11, 8)->nullable();
            $table->json('meta_data')->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_login_attempts');
    }
};
