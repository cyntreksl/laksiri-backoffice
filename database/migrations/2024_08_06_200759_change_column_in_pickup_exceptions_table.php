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
        if (Schema::hasColumn('pickup_exceptions', 'picker_note')) {
            Schema::table('pickup_exceptions', function (Blueprint $table) {
                $table->dropColumn('picker_note');
            });
        }

        Schema::table('pickup_exceptions', function (Blueprint $table) {
            $table->unsignedBigInteger('exception_name_id')
                ->nullable()
                ->after('branch_id');
            $table->foreignId('zone_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_exceptions', function (Blueprint $table) {
            $table->text('picker_note')->nullable();
            $table->foreignId('zone_id')->change();
        });

        if (Schema::hasColumn('pickup_exceptions', 'exception_name_id')) {
            Schema::table('pickup_exceptions', function (Blueprint $table) {
                $table->dropColumn('exception_name_id');
            });
        }
    }
};
