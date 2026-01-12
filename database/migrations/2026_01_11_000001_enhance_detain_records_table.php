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
        Schema::table('detain_records', function (Blueprint $table) {
            // Add new columns for enhanced detain tracking
            $table->string('action')->default('detain')->after('detain_type'); // 'detain' or 'lift'
            $table->string('detain_reason')->nullable()->after('action');
            $table->string('lift_reason')->nullable()->after('detain_reason');
            $table->text('remarks')->nullable()->after('lift_reason');
            $table->unsignedBigInteger('lifted_by')->nullable()->after('rtf_by');
            $table->timestamp('lifted_at')->nullable()->after('lifted_by');
            $table->string('entity_level')->nullable()->after('lifted_at'); // 'shipment', 'hbl', 'package'
            
            // Add foreign key for lifted_by
            $table->foreign('lifted_by')->references('id')->on('users')->nullOnDelete();
            
            // Add index for better query performance
            $table->index(['is_rtf', 'action', 'created_at']);
            $table->index(['rtfable_type', 'rtfable_id', 'is_rtf']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detain_records', function (Blueprint $table) {
            $table->dropForeign(['lifted_by']);
            $table->dropIndex(['is_rtf', 'action', 'created_at']);
            $table->dropIndex(['rtfable_type', 'rtfable_id', 'is_rtf']);
            $table->dropColumn([
                'action',
                'detain_reason',
                'lift_reason',
                'remarks',
                'lifted_by',
                'lifted_at',
                'entity_level'
            ]);
        });
    }
};
