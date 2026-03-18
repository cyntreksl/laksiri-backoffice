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
        Schema::table('containers', function (Blueprint $table) {
            $table->string('manifest_number')->nullable()->unique()->after('reference');
            $table->timestamp('manifest_generated_at')->nullable()->after('manifest_number');
            $table->unsignedBigInteger('manifest_generated_by')->nullable()->after('manifest_generated_at');
            
            $table->foreign('manifest_generated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->dropForeign(['manifest_generated_by']);
            $table->dropColumn(['manifest_number', 'manifest_generated_at', 'manifest_generated_by']);
        });
    }
};