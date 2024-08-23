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
            $table->after('is_released', function (Blueprint $table) {
                $table->json('released_packages')->nullable();
                $table->timestamp('released_at')
                    ->nullable();
                $table->text('note')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_queues', function (Blueprint $table) {
            $table->dropColumn(['released_at', 'note', 'checked_packages']);
        });
    }
};
