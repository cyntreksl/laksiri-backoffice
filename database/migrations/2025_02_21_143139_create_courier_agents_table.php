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
        Schema::create('courier_agents', function (Blueprint $table) {
            $table->id(); // auto increment
            $table->string('company_name')->unique();
            $table->string('website')->nullable();
            $table->string('contact_number_1')->nullable();
            $table->string('contact_number_2')->nullable();
            $table->string('email')->unique()->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->text('invoice_header')->nullable();
            $table->text('invoice_footer')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_agents');
    }
};
