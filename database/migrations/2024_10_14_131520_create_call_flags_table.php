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
        Schema::create('call_flags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hbl_id');
            $table->foreign('hbl_id')->references('id')->on('hbl')->onDelete('cascade');
            $table->string('caller');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->date('followup_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_flags');
    }
};
