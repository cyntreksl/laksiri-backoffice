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
        if (Schema::hasColumn('container_hbl_package', 'status')) {
            Schema::table('container_hbl_package', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        Schema::table('container_hbl_package', function (Blueprint $table) {
            $table->enum('status', ['draft', 'loaded', 'draft-unload'])
                ->default('draft')
                ->after('hbl_package_id');

            $table->unsignedBigInteger('unloaded_by')
                ->nullable()
                ->after('loaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('container_hbl_package', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('unloaded_by');
        });

        Schema::table('container_hbl_package', function (Blueprint $table) {
            $table->enum('status', ['draft', 'loaded'])
                ->default('draft')
                ->after('hbl_package_id');
        });
    }
};
