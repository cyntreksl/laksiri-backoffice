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
            $table->boolean('demurrage_consent_given')->default(false)->after('is_finance_release_approved');
            $table->unsignedBigInteger('demurrage_consent_by')->nullable()->after('demurrage_consent_given');
            $table->timestamp('demurrage_consent_at')->nullable()->after('demurrage_consent_by');
            $table->text('demurrage_consent_note')->nullable()->after('demurrage_consent_at');
            
            $table->foreign('demurrage_consent_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropForeign(['demurrage_consent_by']);
            $table->dropColumn([
                'demurrage_consent_given',
                'demurrage_consent_by',
                'demurrage_consent_at',
                'demurrage_consent_note',
            ]);
        });
    }
};
