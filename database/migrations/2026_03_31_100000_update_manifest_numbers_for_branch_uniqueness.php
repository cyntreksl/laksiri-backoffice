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
        // Update existing manifest numbers to include branch code prefix
        // Handles multiple old formats:
        // - MF00001 -> MF-{branch_code}-00001
        // - MF0100001 -> MF-{branch_code}-00001
        DB::statement("
            UPDATE containers c
            INNER JOIN branches b ON c.branch_id = b.id
            SET c.manifest_number = CONCAT(
                'MF-', 
                b.branch_code, 
                '-', 
                CASE 
                    WHEN c.manifest_number REGEXP '^MF[0-9]{5}$' THEN SUBSTRING(c.manifest_number, 3)
                    WHEN c.manifest_number REGEXP '^MF[0-9]{2}[0-9]{5}$' THEN SUBSTRING(c.manifest_number, 5)
                    ELSE SUBSTRING(c.manifest_number, 3)
                END
            )
            WHERE c.manifest_number IS NOT NULL 
            AND c.manifest_number NOT LIKE 'MF-%-%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert manifest numbers to old format (remove branch code prefix)
        // Format: MF-{branch_code}-00001 -> MF00001
        DB::statement("
            UPDATE containers 
            SET manifest_number = CONCAT('MF', SUBSTRING_INDEX(manifest_number, '-', -1))
            WHERE manifest_number IS NOT NULL 
            AND manifest_number LIKE 'MF-%-%'
        ");
    }
};
