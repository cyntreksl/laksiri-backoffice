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
            $table->string('status')->default('pending')->after('is_released');
            $table->integer('released_package_count')->default(0)->after('status');
            $table->integer('held_package_count')->default(0)->after('released_package_count');
            $table->timestamp('completed_at')->nullable()->after('held_package_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_queues', function (Blueprint $table) {
            $table->dropColumn(['status', 'released_package_count', 'held_package_count', 'completed_at']);
        });
    }
};
