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
        Schema::table('tokens', function (Blueprint $table) {
            $table->boolean('is_cancelled')->default(false)->after('departed_at');
            $table->timestamp('cancelled_at')->nullable()->after('is_cancelled');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('is_cancelled');
            $table->index('cancelled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropIndex(['is_cancelled']);
            $table->dropIndex(['cancelled_by']);
            $table->dropColumn(['is_cancelled', 'cancelled_at', 'cancelled_by', 'cancellation_reason']);
        });
    }
};
