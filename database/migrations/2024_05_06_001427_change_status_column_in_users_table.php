<?php

use App\Enum\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', [UserStatus::ACTIVE->value, UserStatus::DEACTIVATE->value, UserStatus::DELETED->value, UserStatus::INVITED->value, UserStatus::INACTIVE->value])
                ->default(UserStatus::INVITED->value)
                ->after('profile_photo_path')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', [UserStatus::ACTIVE->value, UserStatus::DEACTIVATE->value, UserStatus::DELETED->value, UserStatus::INVITED->value])
                ->default(UserStatus::INVITED->value)
                ->after('profile_photo_path')
                ->change();
        });
    }
};
