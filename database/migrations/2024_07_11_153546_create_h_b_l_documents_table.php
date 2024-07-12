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
        Schema::create('hbl_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hbl_id')->constrained('hbl')->cascadeOnDelete();
            $table->unsignedBigInteger('uploaded_by');
            $table->string('document_name');
            $table->string('document')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h_b_l_documents');
    }
};
