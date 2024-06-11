<?php

use App\Enum\ContainerType;
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
        Schema::table('containers', function (Blueprint $table) {
            $table->string('container_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->enum('container_type', [ContainerType::TwentyFTGeneral->value, ContainerType::TwentyFTHighCube->value, ContainerType::FortyFTGeneral->value, ContainerType::FortyFTHighCube->value])->nullable()->change();
        });
    }
};
