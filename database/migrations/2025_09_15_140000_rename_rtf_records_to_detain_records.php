<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename the table from rtf_records to detain_records
        Schema::rename('rtf_records', 'detain_records');
        
        // Add the detain_type column and update existing records
        Schema::table('detain_records', function (Blueprint $table) {
            $table->string('detain_type')->default('RTF')->after('is_rtf');
        });
        
        // Update existing records to have detain_type = 'RTF'
        DB::table('detain_records')->update(['detain_type' => 'RTF']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the detain_type column
        Schema::table('detain_records', function (Blueprint $table) {
            $table->dropColumn('detain_type');
        });
        
        // Rename the table back to rtf_records
        Schema::rename('detain_records', 'rtf_records');
    }
};
