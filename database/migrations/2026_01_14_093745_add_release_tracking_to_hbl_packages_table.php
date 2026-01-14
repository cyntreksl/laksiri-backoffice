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
            $table->string('release_status')->default('pending')->after('unloaded_by');
            $table->timestamp('released_at')->nullable()->after('release_status');
            $table->unsignedBigInteger('released_by')->nullable()->after('released_at');
            $table->text('release_note')->nullable()->after('released_by');

            $table->foreign('released_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl_packages', function (Blueprint $table) {
            $table->dropForeign(['released_by']);
            $table->dropColumn(['release_status', 'released_at', 'released_by', 'release_note']);
        });
    }
};
