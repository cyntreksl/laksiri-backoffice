<?php

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
        // Step 1: Temporarily convert the `hbl_type` to a string so we can update data
        Schema::table('hbl', function (Blueprint $table) {
            $table->string('hbl_type', 50)->nullable()->change();
        });

        // Step 2: Fix the incorrect data in the column
        DB::table('hbl')
            ->where('hbl_type', 'UBP')
            ->update(['hbl_type' => 'UPB']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 2: Revert the data back to its original value, if needed
        DB::table('hbl')
            ->where('hbl_type', 'UPB')
            ->update(['hbl_type' => 'UBP']);
    }
};
