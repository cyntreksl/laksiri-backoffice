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
        Schema::create('rtf_records', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->morphs('rtfable');
            $table->boolean('is_rtf')->default(false);
            $table->unsignedBigInteger('rtf_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('rtf_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rtf_records');
    }
};
