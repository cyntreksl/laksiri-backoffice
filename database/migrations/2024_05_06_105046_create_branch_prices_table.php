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
        Schema::create('branch_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id')->comment('origin_branch_id');
            $table->integer('destination_branch_id');
            $table->string('cargo_mode');
            $table->enum('price_mode', ['weight', 'volume']);
            $table->string('condition')->comment('> 0');
            $table->string('true_action')->comment('if condition true');
            $table->string('false_action')->comment('if condition false');
            $table->boolean('is_editable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_prices');
    }
};
