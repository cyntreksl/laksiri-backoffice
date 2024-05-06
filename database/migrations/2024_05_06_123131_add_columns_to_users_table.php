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
        Schema::table('users', function (Blueprint $table) {
            $table->string('working_hours_start')
                ->nullable()
                ->after('profile_photo_path');
            $table->string('working_hours_end')
                ->nullable()
                ->after('working_hours_start');
            $table->text('preferred_zone')
                ->nullable()
                ->after('working_hours_end');
            $table->string('contact')
                ->nullable()
                ->after('preferred_zone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['working_hours_start', 'working_hours_end', 'preferred_zone', 'contact']);
        });
    }
};
