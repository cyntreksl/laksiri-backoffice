<?php

use App\Enum\TokenStatus;
use App\Models\Token;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update existing tokens with correct status based on their current state.
     */
    public function up(): void
    {
        // Update cancelled tokens
        DB::table('tokens')
            ->where('is_cancelled', true)
            ->update(['status' => TokenStatus::CANCELLED->value]);

        // Update completed tokens (have departed_at)
        DB::table('tokens')
            ->whereNotNull('departed_at')
            ->where('is_cancelled', false)
            ->update(['status' => TokenStatus::COMPLETED->value]);

        // Update due tokens (created before today, not completed, not cancelled)
        DB::table('tokens')
            ->whereNull('departed_at')
            ->where('is_cancelled', false)
            ->where('created_at', '<', now()->startOfDay())
            ->update(['status' => TokenStatus::DUE->value]);

        // Ongoing tokens (created today, not completed, not cancelled) already have ONGOING as default
        // No need to update them explicitly
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all statuses to ONGOING
        DB::table('tokens')->update(['status' => TokenStatus::ONGOING->value]);
    }
};
