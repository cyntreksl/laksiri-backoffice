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
        Schema::create('unloading_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hbl_package_id')
                ->constrained('hbl_packages')
                ->cascadeOnDelete();
            $table->string('issue');
            $table->string('type')->nullable();
            $table->boolean('is_damaged')->default(false);
            $table->boolean('rtf')->default(false);
            $table->boolean('is_fixed')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unloading_issues');
    }
};
