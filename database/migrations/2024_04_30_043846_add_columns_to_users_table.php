<?php

use App\Enum\UserStatus;
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')
                ->nullable()
                ->change();
            $table->string('email')
                ->nullable()
                ->change();
            $table->string('username')
                ->unique()
                ->after('email');
            $table->unsignedBigInteger('primary_branch_id')
                ->after('id');
            $table->enum('status', [UserStatus::ACTIVE->value, UserStatus::DEACTIVATE->value, UserStatus::DELETED->value, UserStatus::INVITED->value])
                ->default(UserStatus::INVITED->value)
                ->after('profile_photo_path');
            $table->boolean('is_ban')
                ->default(0)
                ->after('status');
            $table->timestamp('last_login_at')
                ->nullable()
                ->after('is_ban');
            $table->timestamp('last_logout_at')
                ->nullable()
                ->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('email')->unique()->change();
            $table->dropColumn(['username', 'primary_branch_id', 'status', 'is_ban', 'last_login_at', 'last_logout_at']);
        });
    }
};
