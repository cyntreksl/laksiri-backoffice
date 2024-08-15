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
        Schema::table('verifications', function (Blueprint $table) {
            $table->after('id', function (Blueprint $table) {
                $table->json('is_checked')
                    ->nullable();
                $table->unsignedBigInteger('verified_by');
                $table->unsignedBigInteger('customer_queue_id');
                $table->unsignedBigInteger('token_id');
                $table->text('note')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verifications', function (Blueprint $table) {
            $table->dropColumn(['is_checked', 'verified_by', 'note', 'customer_queue_id', 'token_id']);
        });
    }
};
