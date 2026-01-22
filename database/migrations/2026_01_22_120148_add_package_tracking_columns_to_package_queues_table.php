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
        Schema::table('package_queues', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('package_queues', 'released_package_count')) {
                $table->integer('released_package_count')->default(0)->after('package_count');
            }
            if (!Schema::hasColumn('package_queues', 'held_package_count')) {
                $table->integer('held_package_count')->nullable()->after('released_package_count');
            }
            if (!Schema::hasColumn('package_queues', 'status')) {
                $table->string('status')->default('pending')->after('is_released');
            }
            if (!Schema::hasColumn('package_queues', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('released_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_queues', function (Blueprint $table) {
            $table->dropColumn(['released_package_count', 'held_package_count', 'status', 'completed_at']);
        });
    }
};
