<?php

use App\Enum\HBLType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->string('hbl_type', 50)->nullable()->change();
        });

        DB::table('hbl')
            ->where('hbl_type', 'UBP')
            ->update(['hbl_type' => 'UPB']);

        Schema::table('hbl', function (Blueprint $table) {
            $table->enum('hbl_type', [HBLType::UPB->value, HBLType::GIFT->value, HBLType::DOOR_TO_DOOR->value])
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            //
        });
    }
};
