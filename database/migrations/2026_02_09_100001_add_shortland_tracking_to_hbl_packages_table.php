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
        Schema::table('hbl_packages', function (Blueprint $table) {
            $table->boolean('is_shortland')->default(false)->after('is_rtf');
            $table->boolean('is_shortland_fixed')->default(false)->after('is_shortland');
            $table->timestamp('shortland_marked_at')->nullable()->after('is_shortland_fixed');
            $table->timestamp('shortland_fixed_at')->nullable()->after('shortland_marked_at');
            $table->foreignId('shortland_marked_by')->nullable()->constrained('users')->nullOnDelete()->after('shortland_fixed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl_packages', function (Blueprint $table) {
            $table->dropColumn(['is_shortland', 'is_shortland_fixed']);
        });
    }
};
