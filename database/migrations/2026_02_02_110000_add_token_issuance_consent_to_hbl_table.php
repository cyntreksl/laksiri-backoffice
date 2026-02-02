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
        Schema::table('hbl', function (Blueprint $table) {
            $table->boolean('token_demurrage_consent_given')->default(false)->after('demurrage_consent_note');
            $table->unsignedBigInteger('token_demurrage_consent_by')->nullable()->after('token_demurrage_consent_given');
            $table->timestamp('token_demurrage_consent_at')->nullable()->after('token_demurrage_consent_by');
            $table->text('token_demurrage_consent_note')->nullable()->after('token_demurrage_consent_at');
            
            $table->foreign('token_demurrage_consent_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropForeign(['token_demurrage_consent_by']);
            $table->dropColumn([
                'token_demurrage_consent_given',
                'token_demurrage_consent_by',
                'token_demurrage_consent_at',
                'token_demurrage_consent_note',
            ]);
        });
    }
};
