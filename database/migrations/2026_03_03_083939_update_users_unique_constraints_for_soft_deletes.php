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
        // Drop existing unique constraints
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropUnique('users_email_unique');
        });

        // Create partial unique indexes that only apply to non-deleted records
        // MySQL doesn't support partial indexes directly, so we use a workaround
        // by creating a unique index on (column, deleted_at) where deleted_at is NULL
        DB::statement('CREATE UNIQUE INDEX users_username_unique ON users (username, (CASE WHEN deleted_at IS NULL THEN 1 ELSE NULL END))');
        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email, (CASE WHEN deleted_at IS NULL THEN 1 ELSE NULL END))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the partial unique indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_username_unique');
            $table->dropIndex('users_email_unique');
        });

        // Restore original unique constraints
        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
            $table->unique('email');
        });
    }
};
