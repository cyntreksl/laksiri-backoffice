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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();
            $table->longText('invoice_header_title')->nullable();
            $table->longText('invoice_header_subtitle')->nullable();
            $table->longText('invoice_header_address')->nullable();
            $table->longText('invoice_header_telephone')->nullable();
            $table->string('invoice_footer_title')->nullable();
            $table->longText('invoice_footer_text')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
