<?php

namespace App\Observers;

use App\Enum\TokenStatus;
use App\Models\Token;
use Illuminate\Support\Facades\Log;

class TokenObserver
{
    /**
     * Handle the Token "updated" event.
     * Log status changes for audit purposes.
     */
    public function updated(Token $token): void
    {
        // Log status changes
        if ($token->isDirty('status')) {
            $oldStatus = $token->getOriginal('status');
            $newStatus = $token->status;

            Log::info('Token status changed', [
                'token_id' => $token->id,
                'token_number' => $token->token,
                'old_status' => $oldStatus,
                'new_status' => $newStatus->value,
                'changed_by' => auth()->id(),
                'changed_at' => now()->toIso8601String(),
            ]);
        }
    }

    /**
     * Handle the Token "created" event.
     * Ensure new tokens start with ONGOING status.
     */
    public function creating(Token $token): void
    {
        if (!isset($token->status)) {
            $token->status = TokenStatus::ONGOING;
        }
    }
}
