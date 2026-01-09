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
        Schema::table('unloading_issues', function (Blueprint $table) {
            // Add remarks field if it doesn't exist
            if (!Schema::hasColumn('unloading_issues', 'remarks')) {
                $table->text('remarks')->nullable()->after('note');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unloading_issues', function (Blueprint $table) {
            if (Schema::hasColumn('unloading_issues', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
