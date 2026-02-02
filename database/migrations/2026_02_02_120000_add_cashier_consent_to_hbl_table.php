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
            $table->boolean('cashier_demurrage_consent_given')->default(false)->after('token_demurrage_consent_note');
            $table->unsignedBigInteger('cashier_demurrage_consent_by')->nullable()->after('cashier_demurrage_consent_given');
            $table->timestamp('cashier_demurrage_consent_at')->nullable()->after('cashier_demurrage_consent_by');
            $table->text('cashier_demurrage_consent_note')->nullable()->after('cashier_demurrage_consent_at');
            
            $table->foreign('cashier_demurrage_consent_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hbl', function (Blueprint $table) {
            $table->dropForeign(['cashier_demurrage_consent_by']);
            $table->dropColumn([
                'cashier_demurrage_consent_given',
                'cashier_demurrage_consent_by',
                'cashier_demurrage_consent_at',
                'cashier_demurrage_consent_note',
            ]);
        });
    }
};
